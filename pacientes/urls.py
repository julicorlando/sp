from django.conf import settings
from django.conf.urls.static import static
from django.urls import include, path
from . import views
from django.contrib.auth import views as auth_views
from .views import excluir_pagamento, editar_evolucao, excluir_arquivo, detalhes_paciente, evolucoes, listar_pacientes, login_view



urlpatterns = [
    # Autenticação
    path('login/', auth_views.LoginView.as_view(), name='login'),
    path('login/', login_view, name='login'),
    path('', listar_pacientes, name='listar_pacientes'),  # Isso redireciona /pacientes/ para a view de listar pacientes
    path('logout/', auth_views.LogoutView.as_view(next_page='index'), name='logout'),
    
    # Pacientes
    path('pacientes/', views.listar_pacientes, name='listar_pacientes'),
    path('pacientes/', listar_pacientes, name='listar_pacientes'),  # Definindo a rota
    path('pacientes/cadastrar/', views.cadastrar_paciente, name='cadastrar_paciente'),
    path('pacientes/<int:paciente_id>/editar/', views.editar_paciente, name='editar_paciente'),
    path('pacientes/<int:pk>/', views.detalhes_paciente, name='detalhes_paciente'),
    path('pacientes/<int:paciente_id>/excluir/', views.excluir_paciente, name='excluir_paciente'),
    path('pacientes/<int:paciente_id>/confirmar_exclusao/', views.confirmar_exclusao_paciente, name='confirmar_exclusao_paciente'),
    path('pacientes/<int:pk>/', detalhes_paciente, name='detalhes_paciente'),

    # Pagamentos
    path('pacientes/<int:paciente_id>/adicionar_pagamento/', views.adicionar_pagamento, name='adicionar_pagamento'),
    path('pacientes/<int:paciente_id>/registrar_pagamento/', views.registrar_pagamento, name='registrar_pagamento'),
    path('pagamentos/<int:paciente_id>/', views.listar_pagamentos, name='listar_pagamentos'),
    path('pacientes/<int:paciente_id>/excluir_pagamento/<int:pagamento_id>/', excluir_pagamento, name='excluir_pagamento'),

    
    
    # Evoluções
    path('pacientes/<int:paciente_id>/evolucoes/', views.evolucoes, name='evolucoes'),
    path('pacientes/<int:paciente_id>/adicionar_evolucao/', views.adicionar_evolucao, name='adicionar_evolucao'),
    path('evolucao/editar/<int:id>/', views.editar_evolucao, name='editar_evolucao'),
    path('pacientes/evolucao/editar/<int:id>/', editar_evolucao, name='editar_evolucao'),
    path('evolucao/<int:evolucao_id>/excluir/', views.excluir_evolucao, name='excluir_evolucao'),
    path('pacientes/<int:paciente_id>/evolucoes/', evolucoes, name='evolucoes'),
    
    # Upload de arquivos
    path('paciente/<int:pk>/upload/', views.upload_arquivo, name='upload_arquivo'),
    path('pacientes/<int:pk>/arquivo/<int:arquivo_pk>/excluir/', views.excluir_arquivo, name='excluir_arquivo'),

    # URLs adicionais
    path('cadastro/', views.cadastro, name="cadastro"),  # Cadastro de novos usuários
]

# Configuração para arquivos de mídia em modo DEBUG
if settings.DEBUG:  # Apenas para desenvolvimento
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
