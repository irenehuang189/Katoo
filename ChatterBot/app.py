from flask import json
from flask import Flask, url_for
app = Flask(__name__)

message = 'apa kabar?'

@app.route('/post_message', methods = ['POST'])
def api_post():
    if request.headers['Content-Type'] == 'application/json':
    	message = request.json
        return "JSON Message: " + json.dumps(request.json)

    else:
        return "415 Unsupported Media Type ;)"


@app.route('/get_message', methods = ['GET'])
def api_get():
    return message
    
if __name__ == '__main__':
    app.run()