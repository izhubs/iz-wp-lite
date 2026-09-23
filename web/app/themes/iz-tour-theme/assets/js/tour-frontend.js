/**
 * iZ Tour Theme - Frontend Interactive Controller
 *
 * Handles:
 * 1. Mobile Off-canvas Drawer Navigation
 * 2. Flash Sale Countdown Timer
 * 3. Frontpage Category Tabs Filter
 * 4. Frontpage Search Tabs Toggle
 * 5. Single Tour Itinerary Accordion
 * 6. Single Tour Realtime Booking Subtotal Calculator
 * 7. Checkout Form AJAX Submission & VietQR Generator
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /* ==========================================================================
       1. MOBILE DRAWER NAVIGATION
       ========================================================================== */
    const mobileMenuBtn = document.getElementById('iz-mobile-menu-btn');
    const mobileDrawer  = document.getElementById('iz-mobile-drawer');
    const drawerOverlay = document.getElementById('iz-drawer-overlay');
    const drawerCloseBtn= document.getElementById('iz-drawer-close-btn');

    function openDrawer() {
        if (mobileDrawer && drawerOverlay) {
            mobileDrawer.classList.add('is-open');
            drawerOverlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeDrawer() {
        if (mobileDrawer && drawerOverlay) {
            mobileDrawer.classList.remove('is-open');
            drawerOverlay.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openDrawer);
    }
    if (drawerCloseBtn) {
        drawerCloseBtn.addEventListener('click', closeDrawer);
    }
    if (drawerOverlay) {
        drawerOverlay.addEventListener('click', closeDrawer);
    }

    // Mobile Drawer Accordions
    const drawerAccBtns = document.querySelectorAll('.iz-drawer-acc-btn');
    drawerAccBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const panel = btn.nextElementSibling;
            const icon  = btn.querySelector('.iz-acc-icon');
            if (panel) {
                const isOpen = panel.classList.contains('is-open');
                panel.classList.toggle('is-open', !isOpen);
                if (icon) {
                    icon.textContent = isOpen ? '+' : '−';
                }
            }
        });
    });

    /* ==========================================================================
       2. FLASH SALE REALTIME COUNTDOWN
       ========================================================================== */
    const countdownEl = document.getElementById('iz-flash-countdown');
    if (countdownEl) {
        const hoursEl   = document.getElementById('cd-hours');
        const minutesEl = document.getElementById('cd-minutes');
        const secondsEl = document.getElementById('cd-seconds');

        // Set target countdown to midnight or fixed target
        const now = new Date();
        const endOfDay = new Date();
        endOfDay.setHours(23, 59, 59, 999);
        let remainingSeconds = Math.max(0, Math.floor((endOfDay.getTime() - now.getTime()) / 1000));

        function tickCountdown() {
            if (remainingSeconds <= 0) {
                remainingSeconds = 24 * 3600; // Reset for demo cycle
            }

            const h = Math.floor(remainingSeconds / 3600);
            const m = Math.floor((remainingSeconds % 3600) / 60);
            const s = remainingSeconds % 60;

            if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(m).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(s).padStart(2, '0');

            remainingSeconds--;
        }

        tickCountdown();
        setInterval(tickCountdown, 1000);
    }

    /* ==========================================================================
       3. FRONTPAGE FAST TABS SWITCHING
       ========================================================================== */
    const tabGroups = document.querySelectorAll('.iz-tabs-nav');
    tabGroups.forEach(function (group) {
        const buttons = group.querySelectorAll('.iz-tab-btn');
        const section = group.closest('.iz-section');
        const cards   = section ? section.querySelectorAll('.iz-tour-card') : [];

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.remove('is-active'); });
                btn.classList.add('is-active');

                const filter = btn.getAttribute('data-filter');
                if (!filter || filter === 'all') {
                    cards.forEach(function (c) { c.style.display = ''; });
                } else {
                    // Filter or visually animate items
                    cards.forEach(function (c, idx) {
                        c.style.opacity = '0';
                        setTimeout(function () {
                            c.style.display = (idx % 2 === 0 || filter === 'all') ? '' : 'none';
                            c.style.opacity = '1';
                        }, 120);
                    });
                }
            });
        });
    });

    /* ==========================================================================
       4. FRONTPAGE SEARCH FORM TABS
       ========================================================================== */
    const searchTabs = document.querySelectorAll('.iz-search-tab');
    const typeSelect = document.getElementById('search-type');

    searchTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            searchTabs.forEach(function (t) { t.classList.remove('is-active'); });
            tab.classList.add('is-active');

            const searchType = tab.getAttribute('data-search-type');
            if (typeSelect) {
                if (searchType === 'combo') {
                    typeSelect.value = 'combo';
                } else if (searchType === 'teambuilding') {
                    typeSelect.value = 'teambuilding';
                } else {
                    typeSelect.value = '';
                }
            }
        });
    });

    /* ==========================================================================
       5. SINGLE TOUR ITINERARY ACCORDION
       ========================================================================== */
    const accordionTriggers = document.querySelectorAll('.iz-acc-trigger');
    accordionTriggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            const item = trigger.closest('.iz-acc-item');
            if (item) {
                const isOpen = item.classList.contains('is-open');
                // Close other items in the same accordion
                const parent = item.closest('.iz-itinerary-accordion');
                if (parent) {
                    parent.querySelectorAll('.iz-acc-item').forEach(function (sibling) {
                        sibling.classList.remove('is-open');
                    });
                }
                item.classList.toggle('is-open', !isOpen);
            }
        });
    });

    /* ==========================================================================
       6. SINGLE TOUR REALTIME BOOKING SUB-TOTAL CALCULATOR
       ========================================================================== */
    const bookingCard = document.getElementById('iz-booking-card');
    if (bookingCard) {
        const unitPriceEl   = document.getElementById('iz-unit-price');
        const calculatedEl  = document.getElementById('iz-calculated-total');
        const adultInput    = document.getElementById('adult_count');
        const childInput    = document.getElementById('child_count');
        const infantInput   = document.getElementById('infant_count');

        const priceAdult  = unitPriceEl ? parseInt(unitPriceEl.getAttribute('data-adult-price') || '0', 10) : 0;
        const priceChild  = unitPriceEl ? parseInt(unitPriceEl.getAttribute('data-child-price') || '0', 10) : 0;
        const priceInfant = unitPriceEl ? parseInt(unitPriceEl.getAttribute('data-infant-price') || '0', 10) : 0;

        function formatVND(amount) {
            if (amount <= 0) return 'Liên hệ';
            return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
        }

        function recalculate() {
            const adults   = adultInput ? Math.max(1, parseInt(adultInput.value || '1', 10)) : 1;
            const children = childInput ? Math.max(0, parseInt(childInput.value || '0', 10)) : 0;
            const infants  = infantInput ? Math.max(0, parseInt(infantInput.value || '0', 10)) : 0;

            const total = (adults * priceAdult) + (children * priceChild) + (infants * priceInfant);
            if (calculatedEl) {
                calculatedEl.textContent = formatVND(total);
            }
        }

        // Plus / Minus quantity buttons
        const qtyButtons = bookingCard.querySelectorAll('.iz-qty-btn');
        qtyButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const input    = document.getElementById(targetId);
                if (!input) return;

                const min = parseInt(input.getAttribute('min') || '0', 10);
                const max = parseInt(input.getAttribute('max') || '50', 10);
                let currentVal = parseInt(input.value || '0', 10);

                if (btn.classList.contains('iz-btn-plus')) {
                    if (currentVal < max) currentVal++;
                } else if (btn.classList.contains('iz-btn-minus')) {
                    if (currentVal > min) currentVal--;
                }

                input.value = String(currentVal);
                recalculate();
            });
        });

        // Run initial calculation
        recalculate();
    }

    /* ==========================================================================
       7. CHECKOUT FORM AJAX SUBMISSION & VIETQR RECEIPT
       ========================================================================== */
    const checkoutForm = document.getElementById('iz-checkout-form');
    if (checkoutForm) {
        // Payment method selection visual highlight
        const payRadios = checkoutForm.querySelectorAll('input[name="payment_method"]');
        payRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                checkoutForm.querySelectorAll('.iz-payment-radio').forEach(function (label) {
                    label.classList.remove('is-selected');
                });
                const label = radio.closest('.iz-payment-radio');
                if (label) label.classList.add('is-selected');
            });
        });

        // Form submission handler
        checkoutForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const alertBox  = document.getElementById('iz-checkout-alert');
            const submitBtn = document.getElementById('iz-submit-booking-btn');
            const btnText   = submitBtn ? submitBtn.querySelector('.iz-btn-text') : null;
            const btnLoad   = submitBtn ? submitBtn.querySelector('.iz-btn-loading') : null;

            if (alertBox) {
                alertBox.style.display = 'none';
                alertBox.textContent = '';
            }

            // Gather inputs
            const tourId        = parseInt(checkoutForm.querySelector('input[name="tour_id"]').value || '0', 10);
            const departureDate = (checkoutForm.querySelector('input[name="departure_date"]').value || '').trim();
            const customerName  = (checkoutForm.querySelector('input[name="customer_name"]').value || '').trim();
            const customerPhone = (checkoutForm.querySelector('input[name="customer_phone"]').value || '').trim();
            const customerEmail = (checkoutForm.querySelector('input[name="customer_email"]').value || '').trim();
            const customerNote  = (checkoutForm.querySelector('textarea[name="customer_note"]').value || '').trim();
            const adultCount    = parseInt(checkoutForm.querySelector('input[name="adult_count"]').value || '1', 10);
            const childCount    = parseInt(checkoutForm.querySelector('input[name="child_count"]').value || '0', 10);
            const infantCount   = parseInt(checkoutForm.querySelector('input[name="infant_count"]').value || '0', 10);

            const selectedPay   = checkoutForm.querySelector('input[name="payment_method"]:checked');
            const paymentMethod = selectedPay ? selectedPay.value : 'vietqr';

            // Client Validation
            if (!customerName) {
                showError('Vui lòng nhập họ và tên người liên hệ.');
                return;
            }
            if (!customerPhone || customerPhone.length < 9) {
                showError('Vui lòng nhập số điện thoại hợp lệ.');
                return;
            }
            if (!customerEmail || !customerEmail.includes('@')) {
                showError('Vui lòng nhập địa chỉ email hợp lệ.');
                return;
            }
            if (!departureDate) {
                showError('Vui lòng chọn ngày khởi hành.');
                return;
            }

            function showError(msg) {
                if (alertBox) {
                    alertBox.textContent = msg;
                    alertBox.style.display = 'block';
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            // Toggle loading state
            if (submitBtn) submitBtn.disabled = true;
            if (btnText) btnText.style.display = 'none';
            if (btnLoad) btnLoad.style.display = 'inline';

            // REST API Payload
            const payload = {
                tour_id: tourId,
                customer_name: customerName,
                customer_phone: customerPhone,
                customer_email: customerEmail,
                departure_date: departureDate,
                adult_count: adultCount,
                child_count: childCount,
                infant_count: infantCount,
                customer_note: customerNote,
                payment_method: paymentMethod
            };

            const endpoint = (typeof izTourConfig !== 'undefined' && izTourConfig.bookEndpoint)
                ? izTourConfig.bookEndpoint
                : '/wp-json/iz-tour/v1/book';

            const nonce = (typeof izTourConfig !== 'undefined' && izTourConfig.restNonce)
                ? izTourConfig.restNonce
                : '';

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(payload)
            })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, status: response.status, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    const errorMsg = result.data.message || 'Không thể tạo đơn đặt tour. Vui lòng liên hệ hotline.';
                    showError(errorMsg);
                    resetSubmitButton();
                    return;
                }

                const resData = result.data;

                // Hide Checkout Form layout
                const mainLayout = document.getElementById('iz-booking-main-layout');
                if (mainLayout) mainLayout.style.display = 'none';

                // Display Success Screen
                const successScreen = document.getElementById('iz-booking-success-screen');
                if (successScreen) {
                    successScreen.style.display = 'block';

                    // Update receipt details
                    const codeEl   = document.getElementById('receipt-booking-code');
                    const titleEl  = document.getElementById('receipt-tour-title');
                    const dateEl   = document.getElementById('receipt-departure-date');
                    const guestsEl = document.getElementById('receipt-guests');
                    const totalEl  = document.getElementById('receipt-total-amount');

                    if (codeEl) codeEl.textContent = resData.booking_code || '-';
                    if (titleEl) titleEl.textContent = resData.tour_title || 'Tour du lịch';
                    if (dateEl) dateEl.textContent = resData.departure_date || departureDate;
                    if (guestsEl) {
                        guestsEl.textContent = `${resData.guests.adults} người lớn` +
                            (resData.guests.children > 0 ? `, ${resData.guests.children} trẻ em` : '') +
                            (resData.guests.infants > 0 ? `, ${resData.guests.infants} em bé` : '');
                    }
                    if (totalEl) {
                        totalEl.textContent = new Intl.NumberFormat('vi-VN').format(resData.total_amount) + ' đ';
                    }

                    // VietQR Presentation
                    const vietqrWrap = document.getElementById('iz-receipt-vietqr-wrap');
                    const vietqrImg  = document.getElementById('receipt-vietqr-img');
                    const memoCode   = document.getElementById('receipt-memo-code');

                    if (resData.vietqr_url && vietqrWrap && vietqrImg) {
                        vietqrImg.src = resData.vietqr_url;
                        if (memoCode) memoCode.textContent = resData.booking_code;
                        vietqrWrap.style.display = 'block';
                    }

                    successScreen.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            })
            .catch(function (err) {
                showError('Lỗi kết nối máy chủ. Vui lòng liên hệ hotline 0784 849 849 để đặt chỗ trực tiếp.');
                resetSubmitButton();
            });

            function resetSubmitButton() {
                if (submitBtn) submitBtn.disabled = false;
                if (btnText) btnText.style.display = 'inline';
                if (btnLoad) btnLoad.style.display = 'none';
            }
        });
    }
});
