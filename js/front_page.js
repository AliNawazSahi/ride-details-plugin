
    function handleRideTypeChange(radio) {
    var expenseTypeDiv = document.getElementById('payment_type');
    if (radio.checked && (radio.value === 'Uber' || radio.value === 'Careem' || radio.value === 'Other' || radio.value === 'Yango' || radio.value === 'Private')) {
        expenseTypeDiv.style.display = 'block';
    } else {
        expenseTypeDiv.style.display = 'none';
    }
}


function handleExpenseTypeChange(radio) {
    var otherExpenseTypeInput = document.getElementById('other-expense-type');

    if (radio.checked && radio.value === 'Other') {
        otherExpenseTypeInput.style.display = 'inline-block';
        otherExpenseTypeInput.setAttribute('required', 'required');
    } else {
        otherExpenseTypeInput.style.display = 'none';
        otherExpenseTypeInput.removeAttribute('required');
    }

    var expenseTypeRadios = document.getElementsByName('expense_type');
    for (var i = 0; i < expenseTypeRadios.length; i++) {
        if (expenseTypeRadios[i] !== radio && expenseTypeRadios[i].value === 'Other') {
            var otherInput = document.getElementById('other-expense-type');
            
            if (otherInput.style.display === 'inline-block') {
                otherInput.style.display = 'none';
                otherInput.removeAttribute('required');
            }
        }
    }
}


document.addEventListener("DOMContentLoaded", function () {
    openTab(null, 'ride');
});

function openTab(evt, tabName) {
    var i, tabcontent, tablinks;

    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].style.backgroundColor = "#f1f1f1";
        tablinks[i].style.color = "#000";
    }

    document.getElementById(tabName).style.display = "block";

    if (evt) {
        evt.currentTarget.style.backgroundColor = "#4CAF50";
        evt.currentTarget.style.color = "#fff";
    } else {
        document.querySelector(".tablinks.active").style.backgroundColor = "#4CAF50";
        document.querySelector(".tablinks.active").style.color = "#fff";
    }

    // Add or remove the "required" attribute based on the active tab
    var rideInput = document.getElementsByName("amount")[0];
    var expenseInput = document.getElementsByName("expense")[0];

    rideInput.required = (tabName === 'ride');
    expenseInput.required = (tabName === 'expense');
}


function validateForm() {
var rideType = document.querySelector('input[name="ride_type"]:checked');
var amount = document.getElementsByName("amount")[0];
var paymentType = document.querySelector('input[name="payment_type"]:checked');

if (document.getElementById('ride').style.display === 'block') {
if (!rideType || !amount || !paymentType) {
    alert('Please fill in all required fields for the Ride Details.');
    return false;
}
} else if (document.getElementById('expense').style.display === 'block') {
var expenseType = document.querySelector('input[name="expense_type"]:checked');
var otherExpenseType = document.getElementById('other-expense-type');
var expenseAmount = document.getElementsByName("expense")[0];

if (!expenseType && !(otherExpenseType.style.display === 'inline-block' && otherExpenseType.value.trim())) {
    alert('Please fill in all required fields for the Expense Details.');
    return false;
}
}

return true;
}


var radioButtons = document.querySelectorAll('input[type="radio"]');
radioButtons.forEach(function (radio) {
radio.addEventListener('change', function () {
    updateSubmitButton();
});
});

function updateSubmitButton() {
    var rideType = document.querySelector('input[name="ride_type"]:checked');
    var amount = document.getElementsByName("amount")[0];
    var paymentType = document.querySelector('input[name="payment_type"]:checked');
    var expenseType = document.querySelector('input[name="expense_type"]:checked');
    var expenseAmount = document.getElementsByName("expense")[0];
    var submitButton = document.getElementById('submit-button');

    // Set the initial color and disabled state
    submitButton.style.backgroundColor = 'rgb(188 195 185)'; // Blue color
    submitButton.disabled = true;

    if (document.getElementById('ride').style.display === 'block') {
        submitButton.disabled = !(rideType && amount && paymentType);
    } else if (document.getElementById('expense').style.display === 'block') {
        submitButton.disabled = !(expenseType && expenseAmount);
    }

    // Update button color based on its disabled state
    submitButton.style.backgroundColor = submitButton.disabled ? 'rgb(188 195 185)' : '#4caf50';
}

// Call the function initially to set the button state
updateSubmitButton();



if (window.history.replaceState) {
    window.history.replaceState( null, null, window.location.href );
}
