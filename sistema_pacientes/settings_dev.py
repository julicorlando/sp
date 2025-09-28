"""
Development settings for sistema_pacientes project.
Uses SQLite database for local development.
"""

from .settings import *
import os

# Override database to use SQLite
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': os.path.join(BASE_DIR, 'db.sqlite3'),
    }
}

# Allow all hosts for development
ALLOWED_HOSTS = ['*']

# Add debug toolbar for development
DEBUG = True