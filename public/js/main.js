// Ensure number of people is at least 1
document.addEventListener('input', (event) => {
    if (event.target.name !== 'people') {
        return;
    }

    const value = Math.max(
        1,
        Number(event.target.value || 1)
    );

    event.target.value = value;
});


// Mobile navigation
const toggle = document.querySelector('[data-nav-toggle]');
const nav = document.querySelector('[data-nav]');

if (toggle && nav) {
    toggle.addEventListener('click', () => {
        const expanded =
            toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute(
            'aria-expanded',
            String(!expanded)
        );

        nav.classList.toggle('is-open', !expanded);
    });
}


// Show and hide password
document
    .querySelectorAll('[data-password-toggle]')
    .forEach((button) => {
        button.addEventListener('click', () => {
            const inputId =
                button.dataset.passwordToggle;

            const input =
                document.getElementById(inputId);

            if (!input) {
                return;
            }

            const isVisible =
                input.type === 'text';

            input.type =
                isVisible
                    ? 'password'
                    : 'text';

            const icon = button.querySelector('i');

            if (icon) {
                icon.className =
                    isVisible
                        ? 'bi bi-eye'
                        : 'bi bi-eye-slash';
            }

            button.setAttribute(
                'aria-label',
                `${isVisible ? 'Show' : 'Hide'} ${button.dataset.passwordLabel || 'password'}`
            );
        });
    });


// Remember login email
const loginEmail =
    document.getElementById('login-email');

const rememberEmail =
    document.querySelector('[data-remember-email]');

const loginForm =
    document.querySelector('[data-login-form]');

if (loginEmail && rememberEmail) {
    const savedEmail =
        localStorage.getItem('cheapdeals_email');

    if (savedEmail && loginEmail.value === '') {
        loginEmail.value = savedEmail;
        rememberEmail.checked = true;
    }

    if (loginForm) {
        loginForm.addEventListener('submit', () => {
            if (rememberEmail.checked) {
                localStorage.setItem(
                    'cheapdeals_email',
                    loginEmail.value.trim()
                );
            } else {
                localStorage.removeItem(
                    'cheapdeals_email'
                );
            }
        });
    }
}


// Shared non-blocking status message
function showToastMessage(message) {
    const toastElement =
        document.getElementById('app-toast');

    if (
        !toastElement ||
        typeof bootstrap === 'undefined'
    ) {
        alert(message);
        return;
    }

    const toastBody =
        toastElement.querySelector('.toast-body');

    if (toastBody) {
        toastBody.textContent = message;
    }

    const toast =
        bootstrap.Toast.getOrCreateInstance(
            toastElement
        );

    toast.show();
}

// Confirm destructive or irreversible operations consistently.
document.querySelectorAll('[data-confirm]').forEach((control) => {
    control.addEventListener('click', (event) => {
        if (!window.confirm(control.dataset.confirm || 'Continue with this action?')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-print-page]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

// Keep card input readable while storing only digits on the server.
document.querySelectorAll('[data-card-number-format]').forEach((input) => {
    input.addEventListener('input', () => {
        const digits = input.value.replace(/\D/g, '').slice(0, 19);
        input.value = digits.replace(/(.{4})/g, '$1 ').trim();
    });
});

// Live summary for the staff telephone-order workflow.
const telephoneOrderForm = document.querySelector('[data-telephone-order-form]');
if (telephoneOrderForm) {
    const telephoneMoney = new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
    });
    const packageSelect = telephoneOrderForm.querySelector('[name="package_id"]');
    const quantityInput = telephoneOrderForm.querySelector('[name="quantity"]');
    const totalOutput = document.getElementById('telephone-total');
    const stockOutput = document.getElementById('telephone-stock');

    const updateTelephoneTotal = () => {
        const option = packageSelect.options[packageSelect.selectedIndex];
        const price = Number(option ? option.dataset.price : 0);
        const stock = Number(option ? option.dataset.stock : 0);
        const quantity = Number(quantityInput.value);

        stockOutput.textContent = option && option.value ? String(stock) : '—';
        totalOutput.textContent = Number.isFinite(price * quantity) && quantity > 0
            ? telephoneMoney.format(price * quantity)
            : telephoneMoney.format(0);
        quantityInput.max = stock > 0 ? String(Math.min(stock, 99)) : '99';
    };

    packageSelect.addEventListener('change', updateTelephoneTotal);
    quantityInput.addEventListener('input', updateTelephoneTotal);
    updateTelephoneTotal();
}


// Add packages to the cart without leaving the current page
function updateCartBubble(count) {
    const bubble = document.querySelector('[data-cart-bubble]');

    if (!bubble) {
        return;
    }

    let badge = bubble.querySelector('.cart-count');

    if (count > 0 && !badge) {
        badge = document.createElement('span');
        badge.className = 'cart-count';
        bubble.appendChild(badge);
    }

    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
        } else {
            badge.remove();
        }
    }

    bubble.setAttribute(
        'aria-label',
        `Open cart with ${count} item${count === 1 ? '' : 's'}`
    );
}

document.querySelectorAll('[data-cart-add]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const originalHtml = button ? button.innerHTML : '';

        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-hourglass-split"></i> Adding...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Could not add this package.');
            }

            updateCartBubble(Number(result.cartCount || 0));
            showToastMessage(result.message);
        } catch (error) {
            showToastMessage(error.message);
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        }
    });
});


// Recalculate cart prices immediately and save quantity in the background
const cartMoney = new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency: 'GBP'
});

function calculateVisibleCartTotal() {
    let total = 0;
    let isValid = true;

    document.querySelectorAll('[data-cart-update] input[name="quantity"]')
        .forEach((input) => {
            const quantity = getValidCartQuantity(input);

            if (quantity === null) {
                isValid = false;
                return;
            }

            total += Number(input.dataset.unitPrice || 0) * quantity;
        });

    const subtotal = document.querySelector('[data-cart-subtotal]');
    if (subtotal) {
        subtotal.textContent = isValid ? cartMoney.format(total) : '—';
    }

    setCartPaymentAvailability(isValid);
}

function getValidCartQuantity(input) {
    const rawValue = input.value.trim();
    const quantity = Number(rawValue);

    if (
        rawValue === ''
        || !Number.isInteger(quantity)
        || quantity < 1
        || quantity > 99
    ) {
        return null;
    }

    return quantity;
}

function setCartPaymentAvailability(isValid) {
    const paymentButton = document.querySelector('[data-cart-payment]');
    const warning = document.querySelector('[data-cart-warning]');

    if (paymentButton) {
        paymentButton.classList.toggle('is-disabled', !isValid);
        paymentButton.setAttribute('aria-disabled', String(!isValid));
    }

    if (warning) {
        warning.classList.toggle('is-visible', !isValid);
    }
}

document.querySelectorAll('[data-cart-update]').forEach((form) => {
    const input = form.querySelector('input[name="quantity"]');
    const item = form.closest('[data-cart-item]');
    const lineTotal = item ? item.querySelector('.cart-line-total') : null;
    const status = form.querySelector('.cart-saving');
    let saveTimer;

    if (!input) {
        return;
    }

    const saveQuantity = async () => {
        const quantity = getValidCartQuantity(input);

        if (status) {
            status.textContent = quantity === null
                ? 'Quantity required'
                : 'Saving…';
            status.classList.toggle('is-error', quantity === null);
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Could not update the cart.');
            }

            if (!result.valid) {
                return;
            }

            if (lineTotal) {
                lineTotal.textContent = cartMoney.format(Number(result.lineTotal));
            }

            const subtotal = document.querySelector('[data-cart-subtotal]');
            if (subtotal) {
                subtotal.textContent = cartMoney.format(Number(result.cartTotal));
            }

            updateCartBubble(Number(result.cartCount || 0));
            if (status) {
                status.classList.remove('is-error');
                status.textContent = 'Saved';
                setTimeout(() => {
                    status.textContent = '';
                }, 1200);
            }
        } catch (error) {
            if (status) {
                status.textContent = 'Try again';
            }
            showToastMessage(error.message);
        }
    };

    input.addEventListener('input', () => {
        const quantity = getValidCartQuantity(input);

        if (lineTotal) {
            lineTotal.textContent = quantity === null
                ? '—'
                : cartMoney.format(Number(input.dataset.unitPrice || 0) * quantity);
        }

        if (status) {
            status.textContent = quantity === null ? 'Quantity required' : '';
            status.classList.toggle('is-error', quantity === null);
        }

        calculateVisibleCartTotal();

        clearTimeout(saveTimer);
        saveTimer = setTimeout(saveQuantity, 450);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(saveTimer);
        saveQuantity();
    });
});

const cartPaymentButton = document.querySelector('[data-cart-payment]');
if (cartPaymentButton) {
    cartPaymentButton.addEventListener('click', (event) => {
        if (cartPaymentButton.getAttribute('aria-disabled') === 'true') {
            event.preventDefault();
            showToastMessage(
                'Please enter a quantity from 1 to 99 before payment.'
            );
        }
    });
}


// Premium checkout: payment card selection
const paymentCheckout = document.querySelector('[data-payment-checkout]');

if (paymentCheckout) {
    const newCardFields = paymentCheckout.querySelector('[data-new-card-fields]');
    const newCardToggle = paymentCheckout.querySelector('[data-new-card-toggle]');
    const paymentOptions = paymentCheckout.querySelectorAll(
        'input[name="payment_method"]'
    );

    const updateCardFields = () => {
        const selected = paymentCheckout.querySelector(
            'input[name="payment_method"]:checked'
        );
        const useNewCard = selected && selected.value === 'new_card';

        if (newCardFields) {
            newCardFields.classList.toggle('is-open', Boolean(useNewCard));
        }
    };

    if (newCardToggle) {
        newCardToggle.addEventListener('click', (event) => {
            const newCardOption = newCardToggle.querySelector(
                'input[name="payment_method"][value="new_card"]'
            );

            if (!newCardOption) {
                return;
            }

            event.preventDefault();

            if (newCardOption.checked) {
                const savedCardFallback = [...paymentOptions].find(
                    (option) => option.value !== 'new_card' && !option.disabled
                );

                newCardOption.checked = false;
                if (savedCardFallback) {
                    savedCardFallback.checked = true;
                }
            } else {
                paymentOptions.forEach((option) => {
                    option.checked = option === newCardOption;
                });
            }

            updateCardFields();
        });
    }

    paymentOptions.forEach((option) => {
        option.addEventListener('change', updateCardFields);
    });
    updateCardFields();

    const cardNumber = paymentCheckout.querySelector('input[name="card_number"]');
    if (cardNumber) {
        cardNumber.addEventListener('input', () => {
            const digits = cardNumber.value.replace(/\D/g, '').slice(0, 19);
            cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    const expiry = paymentCheckout.querySelector('input[name="card_expiry"]');
    if (expiry) {
        expiry.addEventListener('input', () => {
            const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
            expiry.value = digits.length > 2
                ? `${digits.slice(0, 2)}/${digits.slice(2)}`
                : digits;
        });
    }

    const sampleCardButtons = paymentCheckout.querySelectorAll(
        '[data-payment-test-card]'
    );
    sampleCardButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const newCardOption = paymentCheckout.querySelector(
                'input[name="payment_method"][value="new_card"]'
            );
            const cardType = paymentCheckout.querySelector('[name="card_type"]');
            const cardNumber = paymentCheckout.querySelector('[name="card_number"]');
            const cardExpiry = paymentCheckout.querySelector('[name="card_expiry"]');
            const cardCvv = paymentCheckout.querySelector('[name="cvv"]');

            if (!newCardOption || !cardType || !cardNumber || !cardExpiry || !cardCvv) {
                return;
            }

            paymentOptions.forEach((option) => {
                option.checked = option === newCardOption;
            });
            cardType.value = button.dataset.cardType || '';
            cardNumber.value = (button.dataset.cardNumber || '')
                .replace(/(.{4})/g, '$1 ')
                .trim();
            cardExpiry.value = '12/29';
            cardCvv.value = '123';
            sampleCardButtons.forEach((choice) => {
                choice.classList.toggle('is-selected', choice === button);
                choice.setAttribute('aria-pressed', String(choice === button));
            });
            updateCardFields();
            cardType.focus();
        });
        button.setAttribute('aria-pressed', 'false');
    });

    const offerToggle = paymentCheckout.querySelector('[data-offer-toggle]');
    const offerEntry = paymentCheckout.querySelector('[data-offer-entry]');
    const offerInput = paymentCheckout.querySelector('[data-offer-input]');
    const applyOfferButton = paymentCheckout.querySelector('[data-apply-offer]');
    const removeOfferButton = paymentCheckout.querySelector('[data-remove-offer]');
    const offerMessage = paymentCheckout.querySelector('[data-offer-message]');
    const offerChoices = [...paymentCheckout.querySelectorAll('[data-offer-code]')];

    if (offerToggle && offerEntry) {
        offerToggle.addEventListener('click', () => {
            offerEntry.classList.toggle('is-open');
            offerToggle.classList.toggle('is-open');
            if (offerEntry.classList.contains('is-open')) {
                offerInput.focus();
            }
        });
    }

    offerChoices.forEach((choice) => {
        choice.addEventListener('click', () => {
            offerInput.value = choice.dataset.offerCode;
            offerInput.focus();
        });
    });

    if (offerInput) {
        offerInput.addEventListener('input', () => {
            offerInput.value = offerInput.value.toUpperCase().replace(/\s+/g, '');
            offerMessage.classList.remove('is-success', 'is-error');
        });
    }

    if (applyOfferButton && offerInput) {
        applyOfferButton.addEventListener('click', () => {
            const code = offerInput.value.trim().toUpperCase();
            const selected = offerChoices.find(
                (choice) => choice.dataset.offerCode.toUpperCase() === code
            );

            if (!selected) {
                offerMessage.textContent = 'This code is not available for the selected package.';
                offerMessage.classList.remove('is-success');
                offerMessage.classList.add('is-error');
                return;
            }

            const percent = Number(selected.dataset.offerPercent);
            const subtotal = Number(paymentCheckout.dataset.subtotal);
            const appPercent = Number(paymentCheckout.dataset.appDiscountPercent || 15);
            const appDiscount = Math.round(subtotal * appPercent) / 100;
            const offerDiscount = Math.round((subtotal - appDiscount) * percent) / 100;
            const discount = appDiscount + offerDiscount;
            const finalTotal = Math.max(0, subtotal - discount);
            const discountAmount = paymentCheckout.querySelector('[data-discount-amount]');
            const discountLabel = paymentCheckout.querySelector('[data-discount-label]');
            const finalTotalElement = paymentCheckout.querySelector('[data-final-total]');
            const payButtonTotal = paymentCheckout.querySelector('[data-pay-button-total]');

            discountAmount.textContent = `−£${discount.toFixed(2)}`;
            discountLabel.innerHTML = `${appPercent}% app discount + ${code} offer <small>(${percent}% applied second)</small>`;
            finalTotalElement.textContent = `£${finalTotal.toFixed(2)}`;
            payButtonTotal.textContent = `£${finalTotal.toFixed(2)}`;
            offerMessage.textContent = `${code} applied successfully.`;
            offerMessage.classList.remove('is-error');
            offerMessage.classList.add('is-success');
            offerChoices.forEach((choice) => {
                choice.classList.toggle('is-selected', choice === selected);
            });
        });
    }

    if (removeOfferButton && offerInput) {
        removeOfferButton.addEventListener('click', () => {
            const subtotal = Number(paymentCheckout.dataset.subtotal);
            const discountAmount = paymentCheckout.querySelector('[data-discount-amount]');
            const discountLabel = paymentCheckout.querySelector('[data-discount-label]');
            const finalTotalElement = paymentCheckout.querySelector('[data-final-total]');
            const payButtonTotal = paymentCheckout.querySelector('[data-pay-button-total]');

            const appPercent = Number(paymentCheckout.dataset.appDiscountPercent || 15);
            const appDiscount = Math.round(subtotal * appPercent) / 100;
            const finalTotal = Math.max(0, subtotal - appDiscount);
            offerInput.value = '';
            discountAmount.textContent = `−£${appDiscount.toFixed(2)}`;
            discountLabel.textContent = `${appPercent}% app discount`;
            finalTotalElement.textContent = `£${finalTotal.toFixed(2)}`;
            payButtonTotal.textContent = `£${finalTotal.toFixed(2)}`;
            offerMessage.textContent = 'Offer removed. The automatic 15% app discount remains.';
            offerMessage.classList.remove('is-success', 'is-error');
            offerChoices.forEach((choice) => choice.classList.remove('is-selected'));
        });
    }
}

const profileCardForm = document.querySelector('[data-add-card-form]');
if (profileCardForm) {
    const verifyButton = profileCardForm.querySelector('[data-visacheck-verify]');
    const saveButton = profileCardForm.querySelector('[data-visacheck-save]');
    const status = profileCardForm.querySelector('[data-visacheck-status]');
    const fields = [...profileCardForm.querySelectorAll(
        'select[name="card_type"], input[name="card_number"], input[name="card_expiry"], input[name="cvv"]'
    )];

    const resetProfileVerification = () => {
        saveButton.disabled = true;
        status.classList.remove('is-checking', 'is-approved', 'is-declined');
        status.querySelector('i').className = 'bi bi-info-circle';
        status.querySelector('span').textContent = 'Card has not been verified.';
    };

    fields.forEach((field) => {
        field.addEventListener('input', resetProfileVerification);
        field.addEventListener('change', resetProfileVerification);
    });

    profileCardForm.querySelectorAll('[data-visacheck-test-card]').forEach((button) => {
        button.addEventListener('click', () => {
            const type = profileCardForm.querySelector('select[name="card_type"]');
            const number = profileCardForm.querySelector('input[name="card_number"]');
            const expiry = profileCardForm.querySelector('input[name="card_expiry"]');
            const cvv = profileCardForm.querySelector('input[name="cvv"]');
            const digits = button.dataset.cardNumber;

            type.value = button.dataset.cardType;
            number.value = digits.replace(/(.{4})/g, '$1 ').trim();
            expiry.value = '12/29';
            cvv.value = '123';
            resetProfileVerification();

            profileCardForm.querySelectorAll('[data-visacheck-test-card]').forEach(
                (choice) => choice.classList.toggle('is-selected', choice === button)
            );
        });
    });

    verifyButton.addEventListener('click', async () => {
        if (!profileCardForm.reportValidity()) {
            return;
        }

        verifyButton.disabled = true;
        saveButton.disabled = true;
        status.classList.remove('is-approved', 'is-declined');
        status.classList.add('is-checking');
        status.querySelector('i').className = 'bi bi-arrow-repeat';
        status.querySelector('span').textContent = 'VISAcheck is checking the card…';

        try {
            const response = await fetch(profileCardForm.dataset.visacheckUrl, {
                method: 'POST',
                body: new FormData(profileCardForm),
                credentials: 'same-origin',
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();

            status.classList.remove('is-checking');
            if (!response.ok || !result.ok) {
                throw new Error(result.message || 'VISAcheck declined this card.');
            }

            status.classList.add('is-approved');
            status.querySelector('i').className = 'bi bi-patch-check-fill';
            status.querySelector('span').textContent =
                `Approved · Reference ${result.reference}`;
            saveButton.disabled = false;
        } catch (error) {
            status.classList.remove('is-checking');
            status.classList.add('is-declined');
            status.querySelector('i').className = 'bi bi-x-circle-fill';
            status.querySelector('span').textContent =
                error.message || 'VISAcheck could not verify this card.';
        } finally {
            verifyButton.disabled = false;
        }
    });
}


// Close and clear the add-card form on the profile page
const addCardForm = document.querySelector('[data-add-card-form]');
const cancelAddCard = document.querySelector('[data-card-form-cancel]');

if (addCardForm && cancelAddCard) {
    cancelAddCard.addEventListener('click', () => {
        addCardForm.reset();

        addCardForm
            .querySelectorAll('.is-invalid')
            .forEach((field) => field.classList.remove('is-invalid'));
    });
}
