import urllib2
from chatterbot.trainers import ChatterBotCorpusTrainer
from chatterbot import ChatBot

chatterbot = ChatBot("Training Example")

class chatterBot:
	def __init__(self):
		self.response = ''
		chatterbot.set_trainer(ChatterBotCorpusTrainer)

		chatterbot.train(
		   "chatterbot.corpus.indonesia"
		)

	def get_response(self, request):
		self.response = chatterbot.get_response(request)


# cb = ChatterBot()
# cb.get_response("apa kabar?")
# print cb.response