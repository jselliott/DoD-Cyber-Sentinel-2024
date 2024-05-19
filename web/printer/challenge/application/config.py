class Config(object):
    SECRET_KEY = b"5aDHYI1X9N9fH3Ip804C6e4Mkp9MiwV0"
    #DEV_PASSWORD = "".join([random.choice(string.ascii_letters) for i in range(16)])

class ProductionConfig(Config):
    pass

class DevelopmentConfig(Config):
    DEBUG = True

class TestingConfig(Config):
    TESTING = True