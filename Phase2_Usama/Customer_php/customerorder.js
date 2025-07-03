// This function updates the total price when quantity or currency changes
function updateTotal() {
    var quantityInputs = document.querySelectorAll('.quantity-controls input');
    var currencySelect = document.getElementById('currency');
    var totalDisplay = document.getElementById('totalPrice');
    var selectedProduct = document.querySelector('.product-item.selected');
    var basePrice = 0; // Initialize base price

    if (selectedProduct) {
        var pid = selectedProduct.getAttribute('data-pid');

        // Set base price based on product ID (hardcoded)
        switch(pid) {
            case '1': basePrice = 19.90; break;
            case '2': basePrice = 9.90; break;
            case '3': basePrice = 249.90; break;
            case '4': basePrice = 30.00; break;
            case '5': basePrice = 499.00; break;
            default: basePrice = 0;
        }

        var quantity = 0;
        quantityInputs.forEach(input => {
            // Find the quantity input matching selected product
            if (input.closest('.product-item').getAttribute('data-pid') == pid) {
                quantity = parseInt(input.value) || 0;
            }
        });

        // Get selected currency and apply conversion rate
        var currency = currencySelect.value;
        var rate = (currency == 'HKD') ? 7.8 : (currency == 'EUR') ? 0.82 : 110;

        // Calculate total price in selected currency
        var total = basePrice * quantity * rate;

        // Display the formatted total
        totalDisplay.textContent = 'Total Price: ' + total.toFixed(2) + ' ' + currency;

        // Update hidden form fields for submission
        document.getElementById('pid').value = pid;
        document.getElementById('oqty').value = quantity;
    } else {
        // If no product is selected, reset values
        totalDisplay.textContent = 'Total Price: 0.00 HKD';
        document.getElementById('pid').value = '';
        document.getElementById('oqty').value = 0;
    }
}

// Add click event listeners to all product items
// Clicking on a product will select it
// Buttons inside products (+/-) will change the quantity
// And then call updateTotal()
document.querySelectorAll('.product-item').forEach(item => {
    item.addEventListener('click', function() {
        // Deselect all, then mark this item as selected
        document.querySelectorAll('.product-item').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        updateTotal();
    });

    // Handle plus and minus buttons
    item.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            if (this.textContent === '-') {
                input.value = Math.max(0, parseInt(input.value) - 1); // Prevent negative
            } else {
                input.value = parseInt(input.value) + 1;
            }
            updateTotal();
        });
    });
});

// Validate that product is selected and quantity is more than 0 before submitting the form
function confirmSelection() {
    var selectedProduct = document.querySelector('.product-item.selected');
    if (!selectedProduct || document.getElementById('oqty').value <= 0) {
        alert('Please select a product and quantity greater than 0!');
        return false; // Prevent form submission
    }
    return true;
}

// Call updateTotal() when the page loads to show the initial price
window.onload = updateTotal;
