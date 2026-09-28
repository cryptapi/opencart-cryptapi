// CryptAPI payment page. Plain JavaScript on purpose: themes and speed plugins
// often load jQuery late or defer it, which used to stop the status checks and
// leave the page loading forever.
(function () {
    'use strict';

    var POLL_INTERVAL = 5000;
    var REQUEST_TIMEOUT = 10000;
    var FAILURES_BEFORE_NOTICE = 3;

    var started = false;

    function all(selector) {
        return Array.prototype.slice.call(document.querySelectorAll(selector));
    }

    function first(selector) {
        return document.querySelector(selector);
    }

    function show(el) {
        if (el) el.style.display = '';
    }

    function hide(el) {
        if (el) el.style.display = 'none';
    }

    function isHidden(el) {
        return window.getComputedStyle(el).display === 'none';
    }

    function remove(selector) {
        all(selector).forEach(function (el) {
            el.parentNode.removeChild(el);
        });
    }

    // url->link() uses the store URL from settings. If that says http:// while the
    // page is served over https (common behind a proxy), the browser blocks the
    // request as mixed content. Same host, so upgrade it.
    function statusUrl(raw) {
        var url = new URL(raw, window.location.href);

        if (window.location.protocol === 'https:' && url.protocol === 'http:' && url.host === window.location.host) {
            url.protocol = 'https:';
        }

        return url.toString();
    }

    function fetchJson(url) {
        var controller = window.AbortController ? new AbortController() : null;
        var timer = controller ? setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT) : null;

        return fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {'Accept': 'application/json'},
            signal: controller ? controller.signal : undefined
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        }).then(function (data) {
            if (!data || data.error) throw new Error((data && data.error) || 'empty response');
            return data;
        }).finally(function () {
            if (timer) clearTimeout(timer);
        });
    }

    function renderHistory(history, data) {
        var table = first('.ca_history_fill');
        var entries = Object.keys(history);

        if (!table || !entries.length) return;

        show(first('.ca_history'));

        all('.ca_history_fill tr:not(.ca_history_header)').forEach(function (row) {
            row.parentNode.removeChild(row);
        });

        var tbody = table.tBodies[0] || table;

        entries.forEach(function (key) {
            var value = history[key];
            var when = new Date(value.timestamp * 1000);
            var lang = document.documentElement.lang || undefined;
            var row = document.createElement('tr');

            var timeCell = document.createElement('td');
            timeCell.appendChild(document.createTextNode(when.toLocaleTimeString(lang)));
            var date = document.createElement('span');
            date.className = 'ca_history_date';
            date.textContent = when.toLocaleDateString(lang);
            timeCell.appendChild(date);

            var paidCell = document.createElement('td');
            paidCell.textContent = value.value_paid + ' ' + data.coin;

            var fiatCell = document.createElement('td');
            var fiat = document.createElement('strong');
            fiat.textContent = data.fiat_symbol_left + value.value_paid_fiat + data.fiat_symbol_right;
            fiatCell.appendChild(fiat);

            row.appendChild(timeCell);
            row.appendChild(paidCell);
            row.appendChild(fiatCell);
            tbody.appendChild(row);
        });
    }

    // Returns true once the page reached a final state (paid or cancelled).
    function render(data) {
        var waitingPayment = first('.waiting_payment');
        var waitingNetwork = first('.waiting_network');
        var paymentDone = first('.payment_done');

        all('.ca_value').forEach(function (el) { el.textContent = data.remaining; });
        all('.ca_fiat_total').forEach(function (el) { el.textContent = data.fiat_remaining; });
        all('.ca_copy.ca_details_copy').forEach(function (el) { el.setAttribute('data-tocopy', data.remaining); });

        if (data.qr_code_value) {
            all('.ca_qrcode.value').forEach(function (el) { el.src = 'data:image/png;base64,' + data.qr_code_value; });
        }

        if (data.show_min_fee === 1) {
            show(first('.ca_notification_remaining'));
        } else {
            hide(first('.ca_notification_remaining'));
        }

        if (data.remaining !== data.crypto_total) {
            show(first('.ca_notification_payment_received'));
            remove('.ca_notification_cancel');

            var amount = first('.ca_notification_ammount');
            if (amount) {
                var fiat = document.createElement('strong');
                fiat.textContent = data.fiat_symbol_left + data.already_paid_fiat + data.fiat_symbol_right;
                amount.textContent = data.already_paid + ' ' + data.coin + ' (';
                amount.appendChild(fiat);
                amount.appendChild(document.createTextNode(')'));
            }
        }

        if (data.order_history) {
            renderHistory(data.order_history, data);
        }

        if (first('.ca_time_refresh')) {
            // The server counter is the source of truth for "seconds until the next
            // rate refresh". Re-sync when the local one expired or drifted by more
            // than a poll, so a refresh shows at once and the timer can't stick at 0.
            var timer = first('.ca_time_seconds_count');
            var serverCounter = parseInt(data.counter, 10);
            var localSeconds = parseInt(timer.getAttribute('data-seconds'), 10);

            if (isNaN(serverCounter) || serverCounter < 0) serverCounter = 0;
            if (isNaN(localSeconds)) localSeconds = 0;

            if (localSeconds <= 0 || Math.abs(localSeconds - serverCounter) > 3) {
                timer.setAttribute('data-seconds', serverCounter);
            }
        }

        if (data.cancelled === 1) {
            remove('.ca_loader');
            hide(first('.ca_payments_wrapper'));
            show(first('.ca_payment_cancelled'));
            hide(first('.ca_progress'));
            return true;
        }

        if (data.is_paid) {
            [waitingPayment, waitingNetwork, paymentDone].forEach(function (el) { if (el) el.classList.add('done'); });
            remove('.ca_loader');
            remove('.ca_payment_notification:not(.ca_status_offline)');

            setTimeout(function () {
                hide(first('.ca_payments_wrapper'));
                hide(first('.ca_payment_processing'));
                show(first('.ca_payment_confirmed'));
            }, 300);

            return true;
        }

        if (data.is_pending === 1) {
            [waitingPayment, waitingNetwork].forEach(function (el) { if (el) el.classList.add('done'); });
            remove('.ca_loader');
            remove('.ca_payment_notification:not(.ca_status_offline)');

            setTimeout(function () {
                hide(first('.ca_payments_wrapper'));
                show(first('.ca_payment_processing'));
            }, 300);
        }

        return false;
    }

    function startPolling(rawUrl) {
        if (started || !rawUrl) return;
        started = true;

        var url = statusUrl(rawUrl);
        var failures = 0;

        // Schedule the next check only after this one finishes, so a slow store
        // never piles up requests.
        function poll() {
            fetchJson(url).then(function (data) {
                failures = 0;
                hide(first('.ca_status_offline'));

                if (!render(data)) {
                    setTimeout(poll, POLL_INTERVAL);
                }
            }).catch(function (error) {
                failures++;

                if (failures >= FAILURES_BEFORE_NOTICE) {
                    show(first('.ca_status_offline'));
                }

                if (window.console) console.log('CryptAPI status check failed:', error);
                setTimeout(poll, POLL_INTERVAL);
            });
        }

        poll();
    }

    function pad(number) {
        return String(Math.floor(number)).padStart(2, '0');
    }

    function tickTimers() {
        var refresh = first('.ca_time_seconds_count');

        if (first('.ca_time_refresh') && refresh) {
            var seconds = parseInt(refresh.getAttribute('data-seconds'), 10) - 1;

            if (isNaN(seconds) || seconds <= 0) {
                refresh.setAttribute('data-seconds', 0);
                refresh.textContent = '00:00';
            } else {
                refresh.setAttribute('data-seconds', seconds);
                refresh.textContent = seconds <= 30
                    ? refresh.getAttribute('data-soon')
                    : pad(seconds % 3600 / 60) + ':' + pad(seconds % 60);
            }
        }

        var notice = first('.ca_notification_cancel');
        var cancel = first('.ca_cancel_timer');

        if (notice && cancel) {
            var left = parseInt(cancel.getAttribute('data-timestamp'), 10) - 1;

            if (isNaN(left) || left <= 0) {
                cancel.setAttribute('data-timestamp', 0);
            } else if (left <= 60) {
                var strong = document.createElement('strong');
                strong.textContent = notice.getAttribute('data-text');
                notice.textContent = '';
                notice.appendChild(strong);
            } else {
                cancel.setAttribute('data-timestamp', left);
                cancel.textContent = pad(left / 3600) + ':' + pad(left % 3600 / 60);
            }
        }
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).catch(function () { legacyCopy(text); });
            return;
        }

        legacyCopy(text);
    }

    function legacyCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.textContent = text;
        textarea.style.position = 'fixed';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
        } catch (e) {
            if (window.console) console.warn('Copy to clipboard failed.', e);
        } finally {
            document.body.removeChild(textarea);
        }
    }

    function bindControls() {
        all('.ca_qrcode_btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                all('.ca_qrcode_btn').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                var withValue = !btn.classList.contains('no_value');
                all('.ca_qrcode.no_value').forEach(withValue ? hide : show);
                all('.ca_qrcode.value').forEach(withValue ? show : hide);
            });
        });

        all('.ca_show_qr').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                var wrapper = first('.ca_qrcode_wrapper');
                var open = first('.ca_show_qr_open');
                var close = first('.ca_show_qr_close');
                var wasActive = link.classList.contains('active');

                if (wrapper) {
                    if (isHidden(wrapper)) show(wrapper); else hide(wrapper);
                }

                link.classList.toggle('active', !wasActive);
                if (close) close.classList.toggle('active', wasActive);
                if (open) open.classList.toggle('active', !wasActive);
            });
        });

        all('.ca_copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                copyToClipboard(btn.getAttribute('data-tocopy'));

                var tip = btn.querySelector('.ca_tooltip.tip');
                var success = btn.querySelector('.ca_tooltip.success');

                show(success);
                hide(tip);

                setTimeout(function () {
                    hide(success);
                    show(tip);
                }, 5000);
            });
        });
    }

    function init() {
        var panel = first('.ca_payment-panel[data-status-url]');

        bindControls();

        if (first('.ca_time_refresh') || first('.ca_notification_cancel')) {
            setInterval(tickTimers, 1000);
        }

        if (panel) {
            startPolling(panel.getAttribute('data-status-url'));
        }
    }

    // Kept for templates overridden before 3.5.0, which call this inline.
    window.check_status = startPolling;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
