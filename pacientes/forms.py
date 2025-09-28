from django import forms
from django.shortcuts import redirect
from .models import Evolucao, Paciente, Arquivo, Pagamento
from django.contrib.auth.forms import UserCreationForm
from django.contrib.auth.models import User

class PacienteForm(forms.ModelForm):
    class Meta:
        model = Paciente
        fields = [
            'nome', 'sexo', 'estado_Civil', 'data_nascimento', 'cpf', 
            'telefone', 'telefone_alternativo', 'endereco', 'email', 
            'possui_filhos', 'filhos_Quantidade', 
            'eh_menor_tutelado', 'nome_responsaveis', 'cpf_responsavel', 
            'endereco_responsavel', 'telefone_responsavel', 'email_responsavel', 'grau_parentesco',
            'atendimento_anterior', 'tipo_atendimento_ofertado', 'motivo_procura_queixa',
            'religião', 'escolaridade', 'trabalha_no_momento', 'profissão', 
            'toma_Algum_Medicamento', 'qual_Medicamento', 'Disponibilidade', 
            'rede_de_apoio', 'contato_de_emergência', 'motivo_e_objetivo', 'observações'
        ]
        
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        # Make guardian fields conditional - they will be shown/hidden with JavaScript
        guardian_fields = [
            'nome_responsaveis', 'cpf_responsavel', 'endereco_responsavel',
            'telefone_responsavel', 'email_responsavel', 'grau_parentesco'
        ]
        for field in guardian_fields:
            self.fields[field].required = False

class ArquivoForm(forms.ModelForm):
    class Meta:
        model = Arquivo
        fields = ['arquivo']  # Certifique-se de que o campo 'arquivo' é um FileField no modelo
        
class NovoUsuarioForm(UserCreationForm):
    email = forms.EmailField(required=True)

    class Meta:
        model = User
        fields = ("username", "email", "password1", "password2")

    def save(self, commit=True):
        user = super(NovoUsuarioForm, self).save(commit=False)
        user.email = self.cleaned_data["email"]
        if commit:
            user.save()
        return user

class PagamentoForm(forms.ModelForm):
    class Meta:
        model = Pagamento
        fields = ['valor', 'forma_pagamento', 'tipo_pagamento', 'recibo_receita_saude']



class EvolucaoForm(forms.ModelForm):
    class Meta:
        model = Evolucao
        fields = ['conteudo']  # Campos que deseja permitir a edição