from flask import Flask, jsonify

app = Flask(__name__)

# Route for converting amount to another currency using a given rate
@app.route('/cost_convert/<float:amount>/<string:currency>/<float:rate>')
def convert_cost(amount, currency, rate):
    # Step 1: Ensure currency is allowed
    allowed_currencies = ["HKD", "EUR", "JPY"]
    if currency not in allowed_currencies:
        return jsonify({
            "result": "rejected",
            "reason": "Currency must be one of: HKD, EUR, JPY"
        })

    # Step 2: Check for valid and positive values
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

    # Step 3: Calculate converted amount
    converted_amount = round(amount * rate, 2)

    # Step 4: Return the result
    return jsonify({
        "result": "accepted",
        "converted_amount": converted_amount,
        "currency": currency
    })

# Run the app on localhost:8080
if __name__ == "__main__":
    app.run(debug=True, host="127.0.0.1", port=8080)