import os
import string
import random
import time

class Config(object):
    SECRET_KEY = os.urandom(50)
    SQLALCHEMY_DATABASE_URI = 'sqlite:///app.db'
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    FLAG = open("/flag.txt","r").read()

class ProductionConfig(Config):
    pass

class DevelopmentConfig(Config):
    DEBUG = True

class TestingConfig(Config):
    TESTING = True