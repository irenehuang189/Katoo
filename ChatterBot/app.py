from flask import Flask, request, jsonify
from chatter_bot import ChatterBot

app = Flask(__name__)
cb = ChatterBot()

@app.route('/get-reply', methods=['POST'])
def get_reply():
	json = request.json
	message = json["message"]
	reply = cb.get_reply(message)
	result = {'reply': str(reply)}
	return jsonify(result)

if __name__ == '__main__':
    app.run()