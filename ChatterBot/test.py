import urllib2
from chatterbot.trainers import ChatterBotCorpusTrainer
from chatterbot import ChatBot

chatterbot = ChatBot("Training Example")
chatterbot.set_trainer(ChatterBotCorpusTrainer)

chatterbot.train(
   "chatterbot.corpus.indonesia"
)

url = "http://localhost:5000/get_message"
message = urllib2.urlopen(url).read()

print chatterbot.get_response(message)