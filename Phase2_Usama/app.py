from flask import Flask, jsonify

app = Flask(__name__)

@app.route('/cost_convert/<amount>/<currency>/<rate>')
def convert_cost(amount, currency, rate):
    # Step 1: Try to convert amount and rate to numbers
    try:
        amount = float(amount)
        rate = float(rate)
    except ValueError:
        return jsonify({"result": "rejected", "reason": "Amount and Rate must be numbers"})

    # Step 2: Check if amount and rate are positive
    if amount <= 0:
        return jsonify({"result": "rejected", "reason": "Amount must be a positive number"})
    if rate <= 0:
        return jsonify({"result": "rejected", "reason": "Rate must be a positive number"})

    # Step 3: Check if currency is allowed
    allowed_currencies = ["HKD", "EUR", "JPY"]
    if currency not in allowed_currencies:
        return jsonify({"result": "rejected", "reason": "Error: Currency must be 'HKD' or 'EUR' or 'JPY'"})

    # Step 4: Calculate converted amount
    converted_amount = amount * rate

    # Step 5: Return the result in JSON
    return jsonify({
        "result": "accepted",
        "converted_amount": converted_amount
    })

# Run the Flask app
if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=8080)