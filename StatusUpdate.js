var request = new XMLHttpRequest();

// Ich verwenden window.onload, da ich sonst im HTMLLinter Fehler erhalte, wegen der NavigationsBar.
// Welches sich sonst außerhalb des <body>'s befinden würde.. so funktioniert es dynamischer und deutlich besser.
window.onload = function() {
    "use strict";

    requestData();
    window.setInterval(requestData, 2000);
}

function requestData() {
    "use strict";

    request.open("GET", "kundenstatus.php");
    request.onreadystatechange = processData;
    request.send(null);
}

function processData() {
    "use strict";

    if (request.readyState === 4) { // Übertragung = DONE
        if (request.status === 200) { // HTTP-Status = OK
            if (request.responseText !== null) {
                process(request.responseText);
            }
        }
    }
}

function clearOldData(container) {
    "use strict";

    Array.from(container.children).forEach(function (child) {
        child.remove();
    });
}

function process(data) {
    "use strict";

    var order = JSON.parse(data);
    var container = document.getElementById("container");
    var txt = document.createElement("p");

    clearOldData(container);

    if (!Array.isArray(order) || order.length === 0) {
        txt.innerText = "Keine Bestellung gefunden!";
        container.appendChild(txt);

        return;
    }

    if (Array.isArray(order)) {
        order.forEach(function (item) {
            txt = document.createElement("p");
            txt.innerText = item.name + " - Status: " + item.status;
            container.appendChild(txt);
        });
    }
}