from flask import json
from flask import Flask, url_for
from chatterBot import chatterBot

app = Flask(__name__)

# cb = chatterBot()
# cb.get_response("apa kabar?")
# message = cb.response
# print message

@app.route('/post_message', methods = ['POST'])
def api_post():
    if request.headers['Content-Type'] == 'application/json':
    	message = request.json
        return "JSON Message: " + json.dumps(request.json)

    else:
        return "415 Unsupported Media Type ;)"


@app.route('/get_message', methods = ['GET'])
def api_get():
	cb = chatterBot()
	cb.get_response("apa kabar?")
	message = cb.response
	return message
    
if __name__ == '__main__':
    app.run()