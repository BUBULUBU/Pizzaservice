let cartPrice = 0;

function updateCartPrice(newPrice, force) {
    "use strict";

    const actualCost = document.getElementById("total_price");

    if (!force) {
        cartPrice += newPrice;
    } else {
        cartPrice = newPrice;
    }

    if (cartPrice < 0) {
        cartPrice = 0;
    }

    actualCost.innerText = cartPrice.toFixed(2);
}

function addToCart(pizzaId, pizzaName, pizzaPrice) {
    "use strict";

    const pizzaCart = document.getElementById("pizzaCart");

    const option = document.createElement("option");

    option.text = pizzaName;
    option.value = pizzaId;
    option.dataset.price = pizzaPrice;
    pizzaCart.appendChild(option);

    updateCartPrice(parseFloat(pizzaPrice), false);

    checkInput();
}

function deleteCart() {
    "use strict";

    const pizzaCart = document.getElementById("pizzaCart");

    Array.from(pizzaCart.options).forEach(function (option) {
        option.remove();
    });

    updateCartPrice(0, true);

    checkInput();
}

function deletePizza() {
    "use strict";

    const pizzaCart = document.getElementById("pizzaCart");

    Array.from(pizzaCart.options).forEach(function (option) {
        if (option.selected) {
            updateCartPrice(-option.dataset.price, false);
            option.remove();
        }
    });
}

function checkInput() {
    "use strict";

    const pizzaCart = document.getElementById("pizzaCart");
    const addressField = document.getElementById("address");
    const orderButton = document.getElementById("order");

    if (addressField.value.length === 0 || pizzaCart.options.length === 0) {
        orderButton.setAttribute("disabled", "");
    } else {
        orderButton.removeAttribute("disabled");
    }
}

function fetchCart() {
    "use strict";

    const pizzaCart = document.getElementById("pizzaCart");

    Array.from(pizzaCart.options).forEach(function (option) {
        option.selected = true;
    });
}