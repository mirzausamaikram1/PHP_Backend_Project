function updateTotal() {
    var quantityInputs = document.querySelectorAll('.quantity-controls input');
    var currencySelect = document.getElementById('currency');
    var totalDisplay = document.getElementById('totalPrice');
    var selectedProduct = document.querySelector('.product-item.selected');
    var basePrice = 0;

    if (selectedProduct) {
        var pid = selectedProduct.getAttribute('data-pid');
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
            if (input.closest('.product-item').getAttribute('data-pid') == pid) {
                quantity = parseInt(input.value) || 0;
            }
        });
        var currency = currencySelect.value;
        var rate = (currency == 'HKD') ? 7.8 : (currency == 'EUR') ? 0.82 : 110;
        var total = basePrice * quantity * rate;
        totalDisplay.textContent = 'Total Price: ' + total.toFixed(2) + ' ' + currency;
        document.getElementById('pid').value = pid;
        document.getElementById('oqty').value = quantity;
    } else {
        totalDisplay.textContent = 'Total Price: 0.00 HKD';
        document.getElementById('pid').value = '';
        document.getElementById('oqty').value = 0;
    }
}

document.querySelectorAll('.product-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.product-item').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
        updateTotal();
    });
    item.querySelectorAll('button').forEach(button => {
        button.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            if (this.textContent === '-') {
                input.value = Math.max(0, parseInt(input.value) - 1);
            } else {
                input.value = parseInt(input.value) + 1;
            }
            updateTotal();
        });
    });
});

function confirmSelection() {
    var selectedProduct = document.querySelector('.product-item.selected');
    if (!selectedProduct || document.getElementById('oqty').value <= 0) {
        alert('Please select a product and quantity greater than 0!');
        return false;
    }
    return true;
}

window.onload = updateTotal;