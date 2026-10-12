/**
 * Campus Cart - Cart Interaction & Payment Modal Script (cart.js)
 */

document.addEventListener("DOMContentLoaded", function () {
  const buyBtn = document.getElementById("btnProceedToBuy");
  const modalBackdrop = document.getElementById("checkoutModal");
  const modalClose = document.getElementById("modalCloseBtn");
  const cancelBtn = document.getElementById("modalCancelBtn");
  const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
  const dynamicFieldsContainer = document.getElementById("dynamicPaymentFields");

  // Open Modal
  if (buyBtn && modalBackdrop) {
    buyBtn.addEventListener("click", function () {
      modalBackdrop.classList.add("open");
    });
  }

  // Close Modal
  function closeModal() {
    if (modalBackdrop) {
      modalBackdrop.classList.remove("open");
    }
  }

  if (modalClose) {
    modalClose.addEventListener("click", closeModal);
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", closeModal);
  }

  // Close when clicking outside modal dialog
  if (modalBackdrop) {
    modalBackdrop.addEventListener("click", function (e) {
      if (e.target === modalBackdrop) {
        closeModal();
      }
    });
  }

  // Close on Escape key
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modalBackdrop && modalBackdrop.classList.contains("open")) {
      closeModal();
    }
  });

  // Dynamic Payment Method Fields
  function updatePaymentFields(method) {
    if (!dynamicFieldsContainer) return;

    if (method === "UPI") {
      dynamicFieldsContainer.innerHTML = `
        <div class="cc-form-group" style="margin-bottom: 0;">
          <label class="cc-form-label" for="upiId">Enter College / Personal UPI ID</label>
          <input type="text" id="upiId" name="upi_id" class="cc-form-input" placeholder="studentname@okaxis / 9876543210@paytm" required>
          <p class="cc-form-help">Supported: Google Pay, PhonePe, Paytm, BHIM</p>
        </div>
      `;
    } else if (method === "Card") {
      dynamicFieldsContainer.innerHTML = `
        <div class="cc-form-group">
          <label class="cc-form-label" for="cardNumber">Card Number</label>
          <input type="text" id="cardNumber" name="card_number" class="cc-form-input" placeholder="4532 •••• •••• 8901" maxlength="19" required>
        </div>
        <div style="display: flex; gap: 10px;">
          <div class="cc-form-group" style="flex: 1; margin-bottom: 0;">
            <label class="cc-form-label" for="cardExp">Valid Thru (MM/YY)</label>
            <input type="text" id="cardExp" name="card_exp" class="cc-form-input" placeholder="08/28" maxlength="5" required>
          </div>
          <div class="cc-form-group" style="flex: 1; margin-bottom: 0;">
            <label class="cc-form-label" for="cardCvv">CVV</label>
            <input type="password" id="cardCvv" name="card_cvv" class="cc-form-input" placeholder="•••" maxlength="3" required>
          </div>
        </div>
      `;
    } else if (method === "COD") {
      dynamicFieldsContainer.innerHTML = `
        <div class="cc-form-group" style="margin-bottom: 0;">
          <label class="cc-form-label" for="pickupSpot">Preferred Campus Meetup Spot</label>
          <select id="pickupSpot" name="pickup_spot" class="cc-form-select">
            <option value="Central Library Steps">Central Library Steps</option>
            <option value="Student Union / Cafeteria">Student Union / Cafeteria</option>
            <option value="Hostel Block A Front Desk">Hostel Block A Front Desk</option>
            <option value="Department Notice Board Lounge">Department Notice Board Lounge</option>
          </select>
          <p class="cc-form-help">Pay in cash or direct UPI directly to seller upon inspecting goods.</p>
        </div>
      `;
    }
  }

  // Listen for changes
  paymentMethodRadios.forEach(function (radio) {
    radio.addEventListener("change", function () {
      updatePaymentFields(this.value);
    });
  });

  // Initialize with selected method
  const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
  if (checkedRadio) {
    updatePaymentFields(checkedRadio.value);
  }
});
