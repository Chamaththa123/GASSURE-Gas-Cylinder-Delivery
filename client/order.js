const pages = document.querySelectorAll('.form-page');
const nextButton = document.getElementById('nextButton');
const prevButton = document.getElementById('prevButton');
const submitButton = document.getElementById('submitButton');
const quantityInput = document.getElementById('quantity');
const decrementButton = document.getElementById('decrement');
const incrementButton = document.getElementById('increment');
const selectedPriceInput = document.getElementById('selected_price');
const deliveryCitySelect = document.getElementById('delivery_city');

// User's session type
const userType = document.getElementById('userType').value; // "Residential" or "Business"
const maxCylinders = userType === "Residential" ? 2 : 5;

let currentPage = 0;

function updatePage() {
    pages.forEach((page, index) => {
        page.classList.toggle('active', index === currentPage);
    });
    prevButton.disabled = currentPage === 0;
    nextButton.style.display = currentPage === pages.length - 1 ? 'none' : 'inline-block';
    submitButton.style.display = currentPage === pages.length - 1 ? 'inline-block' : 'none';
}

nextButton.addEventListener('click', () => {
    if (currentPage === 0) {
        const selectedItem = document.querySelector('input[name="item_id"]:checked');
        if (!selectedItem) {
            Swal.fire({
                icon: 'warning',
                title: 'Select an Item',
                text: 'Please select an item to proceed.',
            });
            return;
        }
        const maxQuantity = Math.min(parseInt(selectedItem.dataset.stock), maxCylinders);
        if (parseInt(quantityInput.value) > maxQuantity) {
            Swal.fire({
                icon: 'error',
                title: 'Quantity Limit Exceeded',
                text: `As a ${userType} Customer, you can only purchase up to ${maxCylinders} cylinders.`,
            });
            return;
        }
        selectedPriceInput.value = selectedItem.dataset.price;
    } else if (currentPage === 1) {
        const deliveryName = document.getElementById('delivery_name').value.trim();
        const deliveryAddress = document.getElementById('delivery_address').value.trim();
        const contact = document.getElementById('contact').value.trim();
        if (!deliveryName || !deliveryAddress || !contact) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Details',
                text: 'Please fill out all required fields.',
            });
            return;
        }
       
        calculateFinalAmount();
    } else if (currentPage === 2) {
        const cardNo = document.getElementById('card_no').value;
        const expMonth = document.getElementById('exp_month').value;
        const expYear = document.getElementById('exp_year').value;
        const cvv = document.getElementById('cvv').value;
        if (!cardNo || !expMonth || !expYear || !cvv) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Payment Details',
                text: 'Please fill out all payment details.',
            });
            return;
        }

        // Validate Card Number: 16 digits, only numbers
        const cardNoRegex = /^\d{16}$/;
        if (!cardNo || !cardNoRegex.test(cardNo)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Card Number',
                text: 'Please enter a valid 16-digit card number.',
            });
            return;
        }

        // Validate CVV: 3 digits, only numbers
        const cvvRegex = /^\d{3}$/;
        if (!cvv || !cvvRegex.test(cvv)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid CVV',
                text: 'Please enter a valid 3-digit CVV.',
            });
            return;
        }

        // Populate the Invoice Section on Page 4
        const selectedItem = document.querySelector('input[name="item_id"]:checked');
        if (!selectedItem) {
            alert("Please select an item before proceeding to the invoice.");
            return;
        }
        const itemId = selectedItem.value;
        const itemPrice = parseFloat(selectedItem.dataset.price);
        const itemDescription = selectedItem.dataset.description;
        const quantity = parseInt(quantityInput.value);
        const totalPrice = (itemPrice * quantity).toFixed(2);
        const deliveryName = document.getElementById('delivery_name').value;
        const deliveryAddress = document.getElementById('delivery_address').value;
        const contact = document.getElementById('contact').value;
        const contact = document.getElementById('contact').value;

        const invoiceDetails = `
        <p><strong>Order Summary</strong></p>
        <p style="font-size:15px; margin-bottom:-15px;">Item Name: ${itemDescription}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Price per Unit: Rs. ${itemPrice.toFixed(2)}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Quantity: ${quantity}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Total Price: Rs. ${totalPrice}</p>
        <hr>
        <p><strong>Delivery Details</strong></p>
        <p style="font-size:15px; margin-bottom:-15px;">Name: ${deliveryName}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Address: ${deliveryAddress}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Contact: ${contact}</p>
        <hr>
        <p><strong>Payment Summary</strong></p>
        <p style="font-size:15px; margin-bottom:-15px;">Card Type: ${document.getElementById('card_type').value}</p>
        <p style="font-size:15px; margin-bottom:-15px;">Card Number: **** **** **** ${document.getElementById('card_no').value.slice(-4)}</p>
    `;

        document.getElementById('invoice-details').innerHTML = invoiceDetails;
    }

    
    currentPage++;
    updatePage();
});

prevButton.addEventListener('click', () => {
    currentPage--;
    updatePage();
});

decrementButton.addEventListener('click', () => {
    const currentValue = parseInt(quantityInput.value, 10);
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
    }
});

incrementButton.addEventListener('click', () => {
    const currentValue = parseInt(quantityInput.value, 10);
    const selectedItem = document.querySelector('input[name="item_id"]:checked');
    const maxQuantity = selectedItem ? Math.min(parseInt(selectedItem.dataset.stock), maxCylinders) : maxCylinders;
    if (currentValue < maxQuantity) {
        quantityInput.value = currentValue + 1;
    } else {
        Swal.fire({
            icon: 'info',
            title: 'Limit Reached',
            text: `As a ${userType} user, you can only purchase up to ${maxCylinders} cylinders.`,
        });
    }
});

document.getElementById('downloadInvoice').style.display = 'none';

document.getElementById('submitButton').addEventListener('click', (event) => {
    event.preventDefault();

    const form = document.getElementById('multiPageForm');
    const formData = new FormData(form);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then((response) => response.text())
    .then((data) => {
        document.getElementById('submitButton').style.display = 'none';
        document.getElementById('prevButton').style.display = 'none';
        document.getElementById('downloadInvoice').style.display = 'inline-block';
        Swal.fire('Success', 'Order placed successfully!', 'success');
    })
    .catch((error) => {
        Swal.fire('Error', 'Something went wrong!', 'error');
    });
});

updatePage();
