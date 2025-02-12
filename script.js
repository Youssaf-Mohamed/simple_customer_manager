document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", function (e) {
      const amountInput = this.querySelector('input[name="amount"]');
      if (amountInput && amountInput.value <= 0) {
        alert("يرجى إدخال مبلغ صحيح أكبر من الصفر");
        e.preventDefault();
      }
    });
  });

  document.querySelectorAll(".delete-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      if (!confirm("هل أنت متأكد من الحذف؟")) {
        e.preventDefault();
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".delete-customer-btn");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const customerId = this.getAttribute("data-id");
      const customerBalance = parseFloat(this.getAttribute("data-balance"));

      Swal.fire({
        title: "هل أنت متأكد؟",
        text: "سيتم حذف هذا العميل بشكل دائم!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "نعم، احذف!",
        cancelButtonText: "إلغاء",
      }).then((result) => {
        if (result.isConfirmed) {
          fetch("./delete-customer.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ id: customerId }),
          })
            .then((response) => {
              console.log("Response Status:", response.status);
              return response.json();
            })
            .then((data) => {
              console.log("Server Response:", data);
              if (data.success) {
                const totalBalanceElement =
                  document.getElementById("total-balance");
                let currentTotalBalance = parseFloat(
                  totalBalanceElement.textContent.replace(/[^0-9.-]+/g, "")
                );
                totalBalanceElement.textContent = formatBalance(
                  currentTotalBalance - customerBalance
                );

                this.closest("tr").remove();

                Swal.fire("تم الحذف!", "تم حذف العميل بنجاح.", "success");
              } else {
                Swal.fire(
                  "خطأ!",
                  data.message || "حدث خطأ أثناء الحذف.",
                  "error"
                );
              }
            })
            .catch((error) => {
              console.error("Error:", error);
              Swal.fire("خطأ!", "حدث خطأ أثناء الاتصال بالخادم.", "error");
            });
        }
      });
    });
  });

  function formatBalance(balance) {
    return balance.toLocaleString("ar-EG", {
      style: "currency",
      currency: "EGP",
    });
  }
});
