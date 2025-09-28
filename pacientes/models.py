from django.db import models
from django.contrib.auth.models import User
from django.utils import timezone

class Paciente(models.Model):
    usuario = models.ForeignKey(User, on_delete=models.CASCADE)  # Vincula ao usuário
    nome = models.CharField(max_length=100)
    telefone = models.CharField(max_length=15)
    telefone_alternativo = models.CharField(max_length=15, blank=True, null=True)  # New: alternative phone
    endereco = models.CharField(max_length=255)
    data_nascimento = models.DateField(null=True, blank=True)
    cpf = models.CharField(max_length=11, unique=True)
    sexo = models.CharField(max_length=10, choices=[('Masc', 'Masculino'), ('Fem', 'Feminino'), ('O', 'Outro')])
    email = models.EmailField()
    # Updated: added "Não se aplica" for minors
    estado_Civil = models.CharField(max_length=255, choices=[
        ('Não Informado', 'Não Informado'), 
        ('Casado(a)', 'Casado(a)'), 
        ('Solteiro(a)', 'Solteiro(a)'), 
        ('Divorciado(a)', 'Divorciado(a)'), 
        ('Viúvo(a)', 'Viúvo(a)'),
        ('Não se aplica', 'Não se aplica')
    ])
    # Updated: simplified to yes/no for "Possui Filhos?"
    possui_filhos = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')], default='Não', verbose_name="Possui Filhos?")
    filhos_Quantidade = models.CharField(max_length=10, blank=True, null=True)
    
    # New: minor/guardian information
    eh_menor_tutelado = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')], default='Não', verbose_name="É menor de idade/tutelado?")
    nome_responsaveis = models.CharField(max_length=200, blank=True, null=True, verbose_name="Nome dos responsáveis")
    cpf_responsavel = models.CharField(max_length=11, blank=True, null=True, verbose_name="CPF do responsável")
    endereco_responsavel = models.CharField(max_length=255, blank=True, null=True, verbose_name="Endereço do responsável")
    telefone_responsavel = models.CharField(max_length=15, blank=True, null=True, verbose_name="Telefone do responsável")
    email_responsavel = models.EmailField(blank=True, null=True, verbose_name="Email do responsável")
    grau_parentesco = models.CharField(max_length=50, blank=True, null=True, verbose_name="Grau de parentesco/relação")
    
    # Updated service fields
    atendimento_anterior = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')], default='Não', verbose_name="Teve atendimento anterior?")
    tipo_atendimento_ofertado = models.CharField(max_length=500, blank=True, null=True, verbose_name="Tipo de Atendimento Ofertado")
    motivo_procura_queixa = models.CharField(max_length=500, blank=True, null=True, verbose_name="Motivo da Procura/Queixa")
    
    # Updated: made religion optional and increased max_length
    religião = models.CharField(max_length=50, blank=True, null=True)
    
    # Updated: added "sem escolaridade" option
    escolaridade = models.CharField(max_length=255, choices=[
        ('Sem escolaridade', 'Sem escolaridade'),
        ('Fundamental', 'Fundamental'), 
        ('Médio', 'Médio'), 
        ('Superior Completo', 'Superior Completo'), 
        ('Superior incompleto', 'Superior incompleto'), 
        ('Pós-Graduação', 'Pós-Graduação'), 
        ('Mestrado', 'Mestrado'), 
        ('Doutorado', 'Doutorado')
    ])
    trabalha_no_momento = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    profissão = models.CharField(max_length=50, blank=True, null=True)
    toma_Algum_Medicamento =models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    qual_Medicamento =  models.CharField(max_length=100, blank=True, null=True)
    Disponibilidade = models.CharField(max_length=100, blank=True, null=True)
    rede_de_apoio = models.CharField(max_length=255, blank=True, null=True, verbose_name="Rede de Apoio")
    contato_de_emergência = models.CharField(max_length=100, blank=True, null=True)
    motivo_e_objetivo = models.CharField(max_length=500, blank=True, null=True)
    observações = models.CharField(max_length=1000, blank=True, null=True)
    usuario = models.ForeignKey(User, on_delete=models.CASCADE, related_name='pacientes')
    def __str__(self):
        return self.nome
    
class Pagamento(models.Model):
    paciente = models.ForeignKey(Paciente, on_delete=models.CASCADE, related_name='pagamentos')    
    data_pagamento = models.DateField(auto_now_add=True)
    valor = models.DecimalField(max_digits=10, decimal_places=2)  
    forma_pagamento = models.CharField(max_length=20, choices=[
        ('Pix', 'Pix'),
        ('Dinheiro', 'Dinheiro'),
        ('Cartão de Crédito', 'Cartão de Crédito'),
        ('Cartão de Débito', 'Cartão de Débito'),
        ('Convênio', 'Convênio'),
        ('Clínica', 'Clínica'),
    ])
    
    # New: Receipt tracking field (legally required)
    recibo_receita_saude = models.BooleanField(default=False, verbose_name="Recibo emitido via Receita Saúde")
    
    # New: Payment type for analysis
    tipo_pagamento = models.CharField(max_length=20, choices=[
        ('Particular', 'Particular'),
        ('Convênio', 'Convênio'),
        ('Clínica', 'Clínica'),
    ], default='Particular', verbose_name="Tipo de Pagamento")

    def __str__(self):
        return f'{self.paciente.nome} - {self.valor} - {self.data_pagamento}'

class Arquivo(models.Model):
    paciente = models.ForeignKey(Paciente, on_delete=models.CASCADE, related_name='arquivos')
    arquivo = models.FileField(upload_to='arquivos/')
    data_upload = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Arquivo de {self.paciente.nome}"

class Evolucao(models.Model):
    paciente = models.ForeignKey(Paciente, on_delete=models.CASCADE)
    conteudo = models.TextField()
    data = models.DateTimeField(default=timezone.now)