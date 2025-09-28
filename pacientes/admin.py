from django.contrib import admin
from .models import Paciente, Pagamento, Arquivo, Evolucao


class PacienteAdmin(admin.ModelAdmin):
    list_display = ('nome', 'cpf', 'telefone', 'eh_menor_tutelado', 'data_nascimento')
    list_filter = ('sexo', 'estado_Civil', 'eh_menor_tutelado', 'escolaridade')
    search_fields = ('nome', 'cpf', 'telefone', 'email')
    readonly_fields = ('usuario',)
    
    fieldsets = (
        ('Informações Básicas', {
            'fields': ('usuario', 'nome', 'sexo', 'data_nascimento', 'cpf', 'estado_Civil')
        }),
        ('Contato', {
            'fields': ('telefone', 'telefone_alternativo', 'endereco', 'email')
        }),
        ('Informações Familiares', {
            'fields': ('possui_filhos', 'filhos_Quantidade')
        }),
        ('Responsável (se menor)', {
            'fields': ('eh_menor_tutelado', 'nome_responsaveis', 'cpf_responsavel', 
                      'endereco_responsavel', 'telefone_responsavel', 'email_responsavel', 
                      'grau_parentesco'),
            'classes': ('collapse',)
        }),
        ('Atendimento', {
            'fields': ('atendimento_anterior', 'tipo_atendimento_ofertado', 
                      'motivo_procura_queixa', 'rede_de_apoio')
        }),
        ('Outras Informações', {
            'fields': ('religião', 'escolaridade', 'trabalha_no_momento', 'profissão')
        }),
        ('Informações Médicas', {
            'fields': ('toma_Algum_Medicamento', 'qual_Medicamento', 'Disponibilidade', 
                      'contato_de_emergência')
        }),
        ('Observações', {
            'fields': ('motivo_e_objetivo', 'observações')
        }),
    )

class PagamentoAdmin(admin.ModelAdmin):
    list_display = ('paciente', 'valor', 'forma_pagamento', 'tipo_pagamento', 
                    'recibo_receita_saude', 'data_pagamento')
    list_filter = ('forma_pagamento', 'tipo_pagamento', 'recibo_receita_saude', 'data_pagamento')
    search_fields = ('paciente__nome', 'paciente__cpf')
    date_hierarchy = 'data_pagamento'

class ArquivoAdmin(admin.ModelAdmin):
    list_display = ('paciente', 'arquivo', 'data_upload')
    list_filter = ('data_upload',)
    search_fields = ('paciente__nome',)

class EvolucaoAdmin(admin.ModelAdmin):
    list_display = ('paciente', 'data', 'conteudo')
    list_filter = ('data',)
    search_fields = ('paciente__nome', 'conteudo')
    date_hierarchy = 'data'

admin.site.register(Paciente, PacienteAdmin)
admin.site.register(Pagamento, PagamentoAdmin)
admin.site.register(Arquivo, ArquivoAdmin)
admin.site.register(Evolucao, EvolucaoAdmin)
