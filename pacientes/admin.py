from django.contrib import admin
from .models import Paciente


class pacienteadmin(admin.ModelAdmin):
    list_display = ('nome',)
    list_display_links = ('nome',)

admin.site.register(Paciente, pacienteadmin)
