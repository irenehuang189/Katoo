import urllib2
from chatterbot.trainers import ChatterBotCorpusTrainer
from chatterbot import ChatBot

chatterbot = ChatBot("Training Example")

class ChatterBot:
	def __init__(self):
		chatterbot.set_trainer(ChatterBotCorpusTrainer)

		chatterbot.train(
		   "chatterbot.corpus.indonesia"
		)

	def get_reply(self, message):
		return chatterbot.get_response(message)

if __name__ == '__main__':
	cb = ChatterBot()
	print cb.get_reply("apa kabar?")