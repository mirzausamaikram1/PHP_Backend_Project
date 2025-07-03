from flask import Flask, jsonify

app = Flask(__name__)

# Route accepts amount and rate as strings to allow both 100 and 100.0
@app.route('/cost_convert/<amount>/<currency>/<rate>')
def convert_cost(amount, currency, rate):
    # Step 1: Try to convert amount and rate to float
    try:
        amount = float(amount)
        rate = float(rate)
    except ValueError:
        return jsonify({
            "result": "rejected",
            "reason": "Amount and Rate must be valid numbers"
        })

    # Step 2: Validate currency
    allowed_currencies = ["HKD", "EUR", "JPY"]
    if currency not in allowed_currencies:
        return jsonify({
            "result": "rejected",
            "reason": "Currency must be one of: HKD, EUR, JPY"
        })

    # Step 3: Validate positive values
    if amount <= 0:
        return jsonify({
            "result": "rejected",
            "reason": "Amount must be a positive number"
        })

    if rate <= 0:
        return jsonify({
            "result": "rejected",
            "reason": "Rate must be a positive number"
        })

    # Step 4: Calculate converted amount
    converted_amount = round(amount * rate, 2)

    # Step 5: Return result
    return jsonify({
        "result": "accepted",
        "converted_amount": converted_amount,
        "currency": currency
    })

# Run the app locally on port 8080
if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=8080)