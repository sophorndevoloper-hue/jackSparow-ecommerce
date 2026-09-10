/**
 * Global Confirmation Modal Handler
 * Replaces native window.confirm() with a modern, dark-theme-native Bootstrap 5 modal.
 */
(function () {
  "use strict";

  var modalEl = null;
  var bsModal = null;
  var currentResolve = null;
  var currentReject = null;
  var currentForm = null;

  var elements = {
    title: null,
    message: null,
    detail: null,
    detailText: null,
    iconWrapper: null,
    icon: null,
    confirmBtn: null,
    confirmBtnText: null,
    cancelBtn: null,
    spinner: null
  };

  function initElements() {
    modalEl = document.getElementById("globalConfirmModal");
    if (!modalEl || !window.bootstrap) {
      return false;
    }

    elements.title = document.getElementById("confirmModalTitle");
    elements.message = document.getElementById("confirmModalMessage");
    elements.detail = document.getElementById("confirmModalDetail");
    elements.detailText = document.getElementById("confirmModalDetailText");
    elements.iconWrapper = document.getElementById("confirmModalIconWrapper");
    elements.icon = document.getElementById("confirmModalIcon");
    elements.confirmBtn = document.getElementById("confirmModalConfirmBtn");
    elements.confirmBtnText = document.getElementById("confirmModalConfirmBtnText");
    elements.cancelBtn = document.getElementById("confirmModalCancelBtn");
    elements.spinner = document.getElementById("confirmModalSpinner");

    bsModal = new window.bootstrap.Modal(modalEl);

    elements.confirmBtn.addEventListener("click", function () {
      // Show loading spinner
      if (elements.spinner) elements.spinner.classList.remove("d-none");
      elements.confirmBtn.disabled = true;
      elements.cancelBtn.disabled = true;

      var resolveFn = currentResolve;
      var formToSubmit = currentForm;

      currentResolve = null;
      currentReject = null;
      currentForm = null;

      if (formToSubmit) {
        // Native form submit bypasses further onsubmit interceptors
        formToSubmit.submit();
      } else if (resolveFn) {
        resolveFn(true);
        bsModal.hide();
      }
    });

    modalEl.addEventListener("hidden.bs.modal", function () {
      // Reset state and spinner
      if (elements.spinner) elements.spinner.classList.add("d-none");
      elements.confirmBtn.disabled = false;
      elements.cancelBtn.disabled = false;

      if (currentResolve) {
        currentResolve(false);
        currentResolve = null;
        currentReject = null;
      }
      currentForm = null;
    });

    return true;
  }

  var ICONS = {
    danger: {
      wrapperClass: "confirm-icon-danger",
      iconClass: "bi bi-trash3-fill",
      btnClass: "btn-danger"
    },
    warning: {
      wrapperClass: "confirm-icon-warning",
      iconClass: "bi bi-exclamation-triangle-fill",
      btnClass: "btn-warning"
    },
    info: {
      wrapperClass: "confirm-icon-info",
      iconClass: "bi bi-info-circle-fill",
      btnClass: "btn-primary"
    }
  };

  /**
   * Format message text to highlight quoted identifiers, e.g. serial numbers or item names.
   */
  function formatMessageHtml(msg) {
    if (!msg) return "";
    // Replace single or double quoted strings with an attractive badge
    return msg.replace(/'([^']+)'|"([^"]+)"/g, function (match, p1, p2) {
      var val = p1 || p2;
      return '<span class="confirm-highlight-pill font-monospace fw-semibold px-2 py-0.5 rounded">' + val + '</span>';
    });
  }

  /**
   * Open the confirmation dialog.
   *
   * @param {Object} options
   * @param {string} [options.title]
   * @param {string} [options.message]
   * @param {string} [options.detail]
   * @param {string} [options.confirmText]
   * @param {string} [options.cancelText]
   * @param {string} [options.type] - 'danger' | 'warning' | 'info'
   * @param {HTMLFormElement} [options.form]
   * @returns {Promise<boolean>}
   */
  window.confirmModal = function (options) {
    options = options || {};

    if (!modalEl && !initElements()) {
      // Fallback to browser confirm if modal DOM is not available
      return Promise.resolve(window.confirm(options.message || "Are you sure?"));
    }

    var type = options.type || "danger";
    var iconConfig = ICONS[type] || ICONS.danger;

    // Reset button classes
    elements.confirmBtn.className = "btn px-4 fw-medium d-inline-flex align-items-center justify-content-center " + iconConfig.btnClass;
    elements.iconWrapper.className = "confirm-icon-wrapper mx-auto mb-3 " + iconConfig.wrapperClass;
    elements.icon.className = iconConfig.iconClass;

    // Set texts
    elements.title.textContent = options.title || (type === "danger" ? "Confirm Deletion" : "Confirm Action");

    if (options.htmlMessage) {
      elements.message.innerHTML = options.htmlMessage;
    } else {
      elements.message.innerHTML = formatMessageHtml(options.message || "Are you sure you want to proceed?");
    }

    // Detail / impact box
    if (options.detail) {
      elements.detailText.textContent = options.detail;
      elements.detail.classList.remove("d-none");
    } else {
      elements.detail.classList.add("d-none");
    }

    elements.confirmBtnText.textContent = options.confirmText || (type === "danger" ? "Delete Permanently" : "Confirm");
    elements.cancelBtn.textContent = options.cancelText || "Cancel";

    currentForm = options.form || null;

    return new Promise(function (resolve, reject) {
      currentResolve = resolve;
      currentReject = reject;
      bsModal.show();
    });
  };

  /**
   * Automatically intercept forms with data-confirm or legacy onsubmit="return confirm(...)"
   */
  function enhanceConfirmationForms() {
    // 1. Intercept forms with data-confirm or data-confirm-title
    document.addEventListener("submit", function (e) {
      var form = e.target;
      if (!form || form.tagName !== "FORM") return;

      var hasExplicitConfirm = form.hasAttribute("data-confirm") || form.hasAttribute("data-confirm-title");
      if (hasExplicitConfirm) {
        e.preventDefault();
        var title = form.getAttribute("data-confirm-title") || "Confirm Deletion";
        var message = form.getAttribute("data-confirm") || form.getAttribute("data-confirm-message");
        var item = form.getAttribute("data-confirm-item");
        if (item && !message) {
          message = "Are you sure you want to permanently delete '" + item + "'?";
        }
        var detail = form.getAttribute("data-confirm-detail");
        var confirmBtn = form.getAttribute("data-confirm-btn") || "Delete Permanently";
        var type = form.getAttribute("data-confirm-type") || "danger";

        window.confirmModal({
          title: title,
          message: message,
          detail: detail,
          confirmText: confirmBtn,
          type: type,
          form: form
        });
      }
    });

    // 2. Intercept click on buttons/links with data-confirm
    document.addEventListener("click", function (e) {
      var btn = e.target.closest("[data-confirm]");
      if (!btn || btn.tagName === "FORM") return;

      // If button is a submit button inside a form with data-confirm, form submit event handles it
      if (btn.type === "submit" && btn.form && btn.form.hasAttribute("data-confirm")) {
        return;
      }

      e.preventDefault();
      var title = btn.getAttribute("data-confirm-title") || "Confirm Action";
      var message = btn.getAttribute("data-confirm");
      var detail = btn.getAttribute("data-confirm-detail");
      var confirmBtn = btn.getAttribute("data-confirm-btn") || "Confirm";
      var type = btn.getAttribute("data-confirm-type") || "danger";

      window.confirmModal({
        title: title,
        message: message,
        detail: detail,
        confirmText: confirmBtn,
        type: type
      }).then(function (confirmed) {
        if (confirmed) {
          if (btn.form) {
            btn.form.submit();
          } else if (btn.tagName === "A" && btn.href) {
            window.location.href = btn.href;
          }
        }
      });
    });

    // 3. Automatically upgrade legacy onsubmit="return confirm(...)" forms across the admin
    var legacyForms = document.querySelectorAll("form[onsubmit*='confirm(']");
    Array.prototype.forEach.call(legacyForms, function (form) {
      var onsubmitAttr = form.getAttribute("onsubmit") || "";
      var match = onsubmitAttr.match(/confirm\(\s*(?:'|")([\s\S]+?)(?:'|")\s*\)/);
      if (match) {
        var msg = match[1].replace(/\\'/g, "'").replace(/\\"/g, '"');
        form.removeAttribute("onsubmit");
        form.setAttribute("data-confirm", msg);

        // Derive an intelligent title from the message if applicable
        if (msg.toLowerCase().indexOf("delete") !== -1 || msg.toLowerCase().indexOf("remove") !== -1) {
          form.setAttribute("data-confirm-title", "Confirm Deletion");
          form.setAttribute("data-confirm-type", "danger");
          form.setAttribute("data-confirm-btn", "Delete Permanently");
        } else if (msg.toLowerCase().indexOf("revoke") !== -1) {
          form.setAttribute("data-confirm-title", "Revoke Approval");
          form.setAttribute("data-confirm-type", "warning");
          form.setAttribute("data-confirm-btn", "Revoke Access");
        } else if (msg.toLowerCase().indexOf("cancel") !== -1) {
          form.setAttribute("data-confirm-title", "Cancel Action");
          form.setAttribute("data-confirm-type", "warning");
          form.setAttribute("data-confirm-btn", "Yes, Cancel");
        }
      }
    });

    // 4. Upgrade legacy button onclick="return confirm(...)"
    var legacyBtns = document.querySelectorAll("button[onclick*='confirm(']");
    Array.prototype.forEach.call(legacyBtns, function (btn) {
      var onclickAttr = btn.getAttribute("onclick") || "";
      var match = onclickAttr.match(/confirm\(\s*(?:'|")([\s\S]+?)(?:'|")\s*\)/);
      if (match) {
        var msg = match[1].replace(/\\'/g, "'").replace(/\\"/g, '"');
        btn.removeAttribute("onclick");
        btn.setAttribute("data-confirm", msg);
        if (msg.toLowerCase().indexOf("delete") !== -1 || msg.toLowerCase().indexOf("remove") !== -1) {
          btn.setAttribute("data-confirm-title", "Confirm Deletion");
          btn.setAttribute("data-confirm-type", "danger");
          btn.setAttribute("data-confirm-btn", "Delete Permanently");
        } else if (msg.toLowerCase().indexOf("cancel") !== -1) {
          btn.setAttribute("data-confirm-title", "Cancel Action");
          btn.setAttribute("data-confirm-type", "warning");
          btn.setAttribute("data-confirm-btn", "Yes, Cancel");
        }
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", enhanceConfirmationForms);
  } else {
    enhanceConfirmationForms();
  }
})();
