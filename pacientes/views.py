import datetime
from django.shortcuts import render, redirect, get_object_or_404
from .models import Paciente, Arquivo, Pagamento, Evolucao
from .forms import EvolucaoForm, PacienteForm, ArquivoForm, NovoUsuarioForm, PagamentoForm
from django.contrib.auth.decorators import login_required
from django.contrib.auth import authenticate, login
from django.utils import timezone
from django.contrib import messages

def login_view(request):
    if request.method == 'POST':
        username = request.POST['username']
        password = request.POST['password']
        user = authenticate(request, username=username, password=password)
        if user is not None:
            login(request, user)
            return redirect('listar_pacientes')  # Redireciona para a URL nomeada
        else:
            # Tratar falha de login (exibir mensagem de erro)
            return render(request, 'login.html', {'error': 'Usuário ou senha inválidos.'})
    return render(request, 'resistration/login.html')

# Homepage
def index(request):
    return render(request, 'pacientes/index.html')

# Cadastro de novo paciente
@login_required
def cadastrar_paciente(request):
    if request.method == "POST":
        form = PacienteForm(request.POST)
        if form.is_valid():
            paciente = form.save(commit=False)
            paciente.usuario = request.user  # Atribui o usuário logado
            paciente.save()
            return redirect('listar_pacientes')
    else:
        form = PacienteForm()
    return render(request, 'pacientes/cadastrar_paciente.html', {'form': form})

# Listar pacientes
@login_required
def listar_pacientes(request):
    pacientes = Paciente.objects.filter(usuario=request.user)
    return render(request, 'pacientes/listar_pacientes.html', {'pacientes' : pacientes})

# Detalhes do paciente
@login_required
def detalhes_paciente(request, pk):
    paciente = get_object_or_404(Paciente, id=pk)
    pagamentos = Pagamento.objects.filter(paciente=paciente)  # Obtenha todos os pagamentos
    return render(request, 'pacientes/detalhes_paciente.html', {'paciente': paciente, 'pagamentos': pagamentos})

# Upload de arquivo
@login_required
def upload_arquivo(request, pk):
    paciente = get_object_or_404(Paciente, pk=pk)
    
    if request.method == "POST":
        form = ArquivoForm(request.POST, request.FILES)
        if form.is_valid():
            arquivo = form.save(commit=False)
            arquivo.paciente = paciente
            arquivo.save()
            return redirect('detalhes_paciente', pk=paciente.pk)
    else:
        form = ArquivoForm()
    
    return render(request, 'pacientes/upload_arquivo.html', {'form': form, 'paciente': paciente})

# Excluir arquivo
@login_required
def excluir_arquivo(request, pk, arquivo_pk):
    paciente = get_object_or_404(Paciente, pk=pk)
    arquivo = get_object_or_404(Arquivo, pk=arquivo_pk, paciente=paciente)
    
    if request.method == "POST":
        arquivo.delete()
        messages.success(request, "Arquivo excluído com sucesso.")
        return redirect('detalhes_paciente', pk=paciente.pk)
    
    return render(request, 'pacientes/excluir_arquivo.html', {'paciente': paciente, 'arquivo': arquivo})

# Excluir paciente
@login_required
def excluir_paciente(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    paciente.delete()
    messages.success(request, "Paciente excluído com sucesso.")
    return redirect('listar_pacientes')

# Confirmar exclusão do paciente
@login_required
def confirmar_exclusao_paciente(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    return render(request, 'pacientes/confirmar_exclusao.html', {'paciente': paciente})

# Editar paciente
@login_required
def editar_paciente(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    
    if request.method == 'POST':
        form = PacienteForm(request.POST, instance=paciente)
        if form.is_valid():
            form.save()
            messages.success(request, "Paciente atualizado com sucesso.")
            return redirect('listar_pacientes')
    else:
        form = PacienteForm(instance=paciente)
    
    return render(request, 'pacientes/editar_paciente.html', {'form': form, 'paciente': paciente})

# Cadastro de novo usuário
def cadastro(request):
    if request.method == "POST":
        form = NovoUsuarioForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)  # Faz login automaticamente
            return redirect("homepage")
    else:
        form = NovoUsuarioForm()
    return render(request, "cadastro.html", {"form": form})

# Adicionar pagamento
@login_required
def adicionar_pagamento(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    
    if request.method == "POST":
        form = PagamentoForm(request.POST)
        if form.is_valid():
            pagamento = form.save(commit=False)
            pagamento.paciente = paciente  # Associar ao paciente correto
            pagamento.save()
            messages.success(request, "Pagamento adicionado com sucesso.")
            return redirect('detalhes_paciente', pk=paciente_id)
    else:
        form = PagamentoForm()
    
    return render(request, 'pacientes/adicionar_pagamento.html', {'form': form, 'paciente': paciente})

# Listar pagamentos
@login_required
def listar_pagamentos(request, paciente_id):
    pagamentos = Pagamento.objects.filter(paciente_id=paciente_id)
    return render(request, 'pagamentos/listar_pagamentos.html', {'pagamentos': pagamentos})

# Registrar pagamento
@login_required
def registrar_pagamento(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    
    if request.method == 'POST':
        form = PagamentoForm(request.POST)
        if form.is_valid():
            pagamento = form.save(commit=False)
            pagamento.paciente = paciente
            pagamento.save()
            messages.success(request, "Pagamento registrado com sucesso.")
            return redirect('detalhes_paciente', paciente_id=paciente.id)
    else:
        form = PagamentoForm()
    
    return render(request, 'pagamentos/registrar_pagamento.html', {'form': form, 'paciente': paciente})

# Excluir pagamento
@login_required
def excluir_pagamento(request, paciente_id, pagamento_id):
    pagamento = get_object_or_404(Pagamento, id=pagamento_id, paciente_id=paciente_id)
    pagamento.delete()
    messages.success(request, "Pagamento excluído com sucesso.")
    return redirect('registrar_pagamento', paciente_id=paciente_id)
    
# Listar evoluções
@login_required
def evolucoes(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    evolucoes = Evolucao.objects.filter(paciente=paciente).order_by('-data')
    return render(request, 'pacientes/evolucoes.html', {'paciente': paciente, 'evolucoes': evolucoes})

# Adicionar evolução
@login_required
def adicionar_evolucao(request, paciente_id):
    paciente = get_object_or_404(Paciente, id=paciente_id)
    
    if request.method == 'POST':
        form = EvolucaoForm(request.POST)
        if form.is_valid():
            evolucao = form.save(commit=False)
            evolucao.paciente = paciente
            evolucao.data = timezone.now()  # Data da evolução
            evolucao.save()
            messages.success(request, "Evolução adicionada com sucesso.")
            return redirect('evolucoes', paciente_id=paciente.id)
    else:
        form = EvolucaoForm()
    
    return render(request, 'pacientes/adicionar_evolucao.html', {'form': form, 'paciente': paciente})

# Editar evolução
@login_required
def editar_evolucao(request, id):
    evolucao = get_object_or_404(Evolucao, pk=id)
    
    if request.method == 'POST':
        form = EvolucaoForm(request.POST, instance=evolucao)
        if form.is_valid():
            form.save()
            return redirect('detalhes_paciente', pk=evolucao.paciente.id)  # Usando 'pk' aqui
    else:
        form = EvolucaoForm(instance=evolucao)
    return render(request, 'pacientes/editar_evolucao.html', {'form': form, 'evolucao': evolucao})
    
# Excluir evolução
@login_required
def excluir_evolucao(request, evolucao_id):
    evolucao = get_object_or_404(Evolucao, id=evolucao_id)
    paciente_id = evolucao.paciente.id
    evolucao.delete()
    messages.success(request, "Evolução excluída com sucesso.")
    return redirect('evolucoes', paciente_id=paciente_id)
