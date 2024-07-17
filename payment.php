<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscribe to Pro Plan</title>
  <style>
    body {
        background-color: #f9f9f9;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .subscription-form {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.401);
    }

    .subscription-form h2 {
      margin-bottom: 10px;
    }

    .subscription-form label {
      display: block;
      margin-bottom: 5px;
    }

    .subscription-form input,
    .subscription-form button {
      width: 95%;
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .subscription-form select {
      width: 101%;
      padding: 8px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .submit {
      width: 105%;
      padding: 8px;
      margin-top: 15px;
      margin-bottom: 10px;
      margin-left: -8px;
    }


    #card-element {
      border: 1px solid #ccc;
      padding: 10px;
      border-radius: 4px;
    }

    #card-errors {
      color: red;
      margin-top: 10px;
    }
  </style>
  <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
  <div class="subscription-form">
    <h2>Subscribe for Typing tutor</h2>
    <p>₹20/month + tax</p>
    <form id="subscription-form">
      <label for="name">Full name</label>
      <input type="text" id="name" name="name" required>
      
      <label for="country">Country or region</label>
      <select id="country" name="country" required>
        <option value="India">India</option>
        <!-- Add other countries as needed -->
      </select>
      
      <label for="address">Address</label>
      <input type="text" id="address" name="address" required>
      
      <label for="card-element">Card</label>
      <div id="card-element"></div>
      
      <div class="submit">
      <button type="submit">Subscribe</button>
      </div>
    </form>
    <div id="card-errors" role="alert"></div>
  </div>

  <script>
    // Replace with your own Stripe publishable key
    const stripe = Stripe('your-publishable-key-here');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const form = document.getElementById('subscription-form');
    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const { token, error } = await stripe.createToken(cardElement);

      if (error) {
        // Display error.message in your UI
        document.getElementById('card-errors').textContent = error.message;
      } else {
        // Send token to your server
        fetch('/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ token: token.id, name: form.name.value, country: form.country.value, address: form.address.value }),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Subscription successful!');
          } else {
            alert('Subscription failed!');
          }
        });
      }
    });
  </script>
</body>
</html>
