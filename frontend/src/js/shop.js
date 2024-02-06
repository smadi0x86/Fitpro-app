document.addEventListener("DOMContentLoaded", () => {
  const addToCartButtons = document.querySelectorAll(".add-to-cart");
  addToCartButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const productId = event.target.getAttribute("data-product-id");
      const productPrice = parseFloat(
        event.target.getAttribute("data-product-price")
      );
      addToCart(productId, productPrice);
      updateBasketCount();
    });
  });
  updateBasketCount();
});

function addToCart(productId, productPrice) {
  let cart = localStorage.getItem("cart")
    ? JSON.parse(localStorage.getItem("cart"))
    : {};

  if (cart[productId]) {
    cart[productId].quantity += 1;
    cart[productId].totalPrice = (
      cart[productId].quantity * productPrice
    ).toFixed(2);
  } else {
    cart[productId] = {
      price: productPrice,
      quantity: 1,
      totalPrice: productPrice.toFixed(2),
    };
  }

  localStorage.setItem("cart", JSON.stringify(cart));
  // alert(`Product ${productId} added to cart!`);
}

function updateBasketCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  let totalCount = 0;
  Object.values(cart).forEach((item) => (totalCount += item.quantity));
  document.getElementById("basket-count").textContent = totalCount;
}
