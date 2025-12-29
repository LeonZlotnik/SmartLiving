import os

class Config(object):
    DEBUG = True
    TESTING = False
    SECRET_KEY = os.getenv("SECRET_KEY", "dev-secret-key")  # fallback solo local
    OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")

    if not OPENAI_API_KEY:
        raise RuntimeError("OPENAI_API_KEY no definida en el entorno")

class DevelopmentConfig(Config):
    DEBUG = True

class TestingConfig(Config):
    TESTING = True

class ProductionConfig(Config):
    DEBUG = False

config = {
   'development': DevelopmentConfig,
   'testing': TestingConfig,
   'production': ProductionConfig
}
