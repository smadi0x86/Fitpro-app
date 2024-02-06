document.addEventListener("DOMContentLoaded", () => {
  displayCartItems();
  const checkoutButton = document.getElementById("checkout-button");
  if (checkoutButton) {
    checkoutButton.addEventListener("click", checkout);
  }
});

function displayCartItems() {
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  const cartItemsContainer = document.querySelector(".cart-items");
  cartItemsContainer.innerHTML = ""; // Empty the container before displaying items

  if (Object.keys(cart).length === 0) {
    const emptyMessage = document.createElement("div");
    emptyMessage.classList.add("empty-cart-message");
    emptyMessage.textContent = "Your cart is empty.";
    cartItemsContainer.appendChild(emptyMessage);
  } else {
    Object.entries(cart).forEach(([productId, productData]) => {
      const productElement = document.createElement("div");
      productElement.classList.add("cart-item");
      productElement.innerHTML = `
        <span>${productId}</span>
        <span>Price: $${productData.price}</span>
        <span>Quantity: ${productData.quantity}</span>
        <span>Total: $${(productData.quantity * productData.price).toFixed(
          2
        )}</span>
        <div>
            <button class="quantity-change" data-product-id="${productId}" data-change="-1">-</button>
            <button class="quantity-change" data-product-id="${productId}" data-change="1">+</button>
            <button class="remove-from-cart" data-product-id="${productId}">Remove</button>
        </div>
      `;
      cartItemsContainer.appendChild(productElement);
    });
  }

  // Attach event listeners to buttons
  document.querySelectorAll(".remove-from-cart").forEach((button) => {
    button.addEventListener("click", () =>
      removeFromCart(button.dataset.productId)
    );
  });

  document.querySelectorAll(".quantity-change").forEach((button) => {
    button.addEventListener("click", () =>
      changeQuantity(
        button.dataset.productId,
        parseInt(button.dataset.change, 10)
      )
    );
  });
}

function removeFromCart(productId) {
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  delete cart[productId];
  updateLocalStorage(cart);
  displayCartItems(); // Refresh cart display
}

function changeQuantity(productId, change) {
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  if (cart[productId]) {
    cart[productId].quantity += change;
    if (cart[productId].quantity <= 0) {
      removeFromCart(productId);
    } else {
      updateLocalStorage(cart);
      displayCartItems(); // Refresh cart display
    }
  }
}

function checkout() {
  const cart = JSON.parse(localStorage.getItem("cart")) || {};
  if (Object.keys(cart).length > 0) {
    initiateStripeCheckout(cart);
  } else {
    alert("Your cart is empty.");
  }
}

function initiateStripeCheckout(cart) {
  // Convert cart object to an array of items expected by the backend
  const cartItems = Object.entries(cart).map(([name, item]) => {
    return {
      name: name,
      price: item.price,
      quantity: item.quantity,
    };
  });

  fetch("https://fitpro.smadi0x86.me/api/create-checkout-session", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${localStorage.getItem("jwtToken")}`,
    },
    body: JSON.stringify({ cart: cartItems }),
  })
    .then((response) => {
      if (response.status === 401) {
        throw new Error("Unauthorized access. Please login to proceed.");
      }
      return response.json();
    })
    .then((session) => {
      var stripe = Stripe(
        "pk_test_51OU5pSKwKKX8toD9ZrxGscsqAvx1Gqruwue560weMxZCXlGcir43I2O8CotheUldpMdvFBbkFbQ0iiDxgjEbeyWk00eEqjGBGs"
      );
      stripe.redirectToCheckout({ sessionId: session.sessionId });
    })
    .catch((error) => {
      console.error("Error:", error);
      alert(error.message);
    });
}

function updateLocalStorage(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
}
