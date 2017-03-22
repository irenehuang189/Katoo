from flask import Flask, request
from chatter_bot import ChatterBot

app = Flask(__name__)
cb = ChatterBot()

@app.route('/get-reply', methods=['GET'])
def get_reply():
    message = request.args.get('m')
    reply = cb.get_reply(message)
    return str(reply)

    
if __name__ == '__main__':
    app.run()