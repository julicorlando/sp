from django.db import models
from django.contrib.auth.models import User
from django.utils import timezone

class Paciente(models.Model):
    usuario = models.ForeignKey(User, on_delete=models.CASCADE)  # Vincula ao usuário
    nome = models.CharField(max_length=100)
    telefone = models.CharField(max_length=15)
    endereco = models.CharField(max_length=255)
    data_nascimento = models.DateField(null=True, blank=True)
    cpf = models.CharField(max_length=11, unique=True)
    sexo = models.CharField(max_length=10, choices=[('Masc', 'Masculino'), ('Fem', 'Feminino'), ('O', 'Outro')])
    email = models.EmailField()
    estado_Civil = models.CharField(max_length=255, choices=[('Não Informado', 'Não Informado'), ('Casado(a)', 'Casado(a)'), ('Solteiro(a)', 'Solteiro(a)'), ('Divorciado(a)', 'Divorciado(a)'), ('Viúvo(a)', 'Viúvo(a)')])
    filhos = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    filhos_Quantidade = models.CharField(max_length=10)
    atendimento= models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    atendimento_Tipo_Tempo_Motivo = models.CharField(max_length=500)
    religião = models.CharField(max_length=20)
    escolaridade = models.CharField(max_length=255, choices=[('Fundamental', 'Fundamental'), ('Médio', 'Médio'), ('Superior Completo', 'Superior Completo'), ('Superior incompleto', 'Superior incompleto'), ('Pós-Grauação', 'Pós-Graduação'), ('Mestrado', 'Mestrado'), ('Dotourado', 'Dotourado')])
    trabalha_no_momento = models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    profissão = models.CharField(max_length=50)
    toma_Algum_Medicamento =models.CharField(max_length=10, choices=[('Sim', 'Sim'), ('Não', 'Não')])
    qual_Medicamento =  models.CharField(max_length=100)
    Disponibilidade = models.CharField(max_length=100)
    rede_de_apoio = models.CharField(max_length=255)
    contato_de_emergência = models.CharField(max_length=100)
    motivo_e_objetivo = models.CharField(max_length=500)
    observações = models.CharField(max_length=1000)
    usuario = models.ForeignKey(User, on_delete=models.CASCADE, related_name='pacientes')
    def __str__(self):
        return self.nome
    
class Pagamento(models.Model):
    paciente = models.ForeignKey(Paciente, on_delete=models.CASCADE, related_name='pagamentos')    
    data_pagamento = models.DateField(auto_now_add=True)
    valor = models.DecimalField(max_digits=10, decimal_places=2)  
    forma_pagamento = models.CharField(max_length=17, choices=[
        ('Pix', 'Pix'),
        ('Dinheiro', 'Dinheiro'),
        ('Cartão de Crédito', 'Cartão de Crédito'),  # Exemplo de um valor longo
        ('Cartão de Débito', 'Cartão de Débito'),
        # Outros valores de escolha
    ])

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