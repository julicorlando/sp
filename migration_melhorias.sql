-- ===================================================================
-- MIGRATION SCRIPT - Sistema de Pacientes - Melhorias Solicitadas
-- ===================================================================
-- Este script atualiza o banco de dados existente com os novos campos
-- Data: 2024
-- Descrição: Implementa melhorias no cadastro e pagamentos conforme solicitação
-- ===================================================================

USE stepsi_db;

-- ===================================================================
-- 1. ALTERAÇÕES NA TABELA PACIENTES
-- ===================================================================

-- Adicionar telefone alternativo
ALTER TABLE pacientes 
ADD COLUMN telefone_alternativo VARCHAR(15) DEFAULT '' 
COMMENT 'Telefone alternativo do paciente';

-- Alterar nome do campo filhos para possui_filhos (melhor semântica)
ALTER TABLE pacientes 
CHANGE COLUMN filhos possui_filhos VARCHAR(10) NOT NULL
COMMENT 'Indica se o paciente possui filhos (Sim/Não)';

-- Remover campo filhos_quantidade (não mais necessário com nova estrutura)
ALTER TABLE pacientes 
DROP COLUMN IF EXISTS filhos_quantidade;

-- Remover campo religião conforme solicitação
ALTER TABLE pacientes 
DROP COLUMN IF EXISTS religiao;

-- Alterar rede_de_apoio para TEXT (melhor estruturação)
ALTER TABLE pacientes 
MODIFY COLUMN rede_de_apoio TEXT DEFAULT ''
COMMENT 'Informações sobre rede de apoio do paciente';

-- Adicionar campos para menor de idade/tutelado
ALTER TABLE pacientes 
ADD COLUMN e_menor_tutelado VARCHAR(10) DEFAULT 'Não'
COMMENT 'Indica se paciente é menor de idade ou tutelado';

ALTER TABLE pacientes 
ADD COLUMN responsavel_nome VARCHAR(100) DEFAULT ''
COMMENT 'Nome do responsável (quando menor/tutelado)';

ALTER TABLE pacientes 
ADD COLUMN responsavel_cpf VARCHAR(11) DEFAULT ''
COMMENT 'CPF do responsável (quando menor/tutelado)';

ALTER TABLE pacientes 
ADD COLUMN responsavel_endereco VARCHAR(255) DEFAULT ''
COMMENT 'Endereço do responsável (quando menor/tutelado)';

ALTER TABLE pacientes 
ADD COLUMN responsavel_contato VARCHAR(15) DEFAULT ''
COMMENT 'Contato do responsável (quando menor/tutelado)';

ALTER TABLE pacientes 
ADD COLUMN responsavel_parentesco VARCHAR(50) DEFAULT ''
COMMENT 'Grau de parentesco do responsável';

-- Separar campos de atendimento conforme nova estrutura
ALTER TABLE pacientes 
ADD COLUMN tipo_atendimento_ofertado VARCHAR(500) DEFAULT ''
COMMENT 'Tipo de atendimento que será ofertado';

ALTER TABLE pacientes 
ADD COLUMN motivo_procura_queixa VARCHAR(500) DEFAULT ''
COMMENT 'Motivo da procura/queixa do paciente';

-- Remover campo antigo (será substituído pelos dois novos)
ALTER TABLE pacientes 
DROP COLUMN IF EXISTS atendimento_tipo_tempo_motivo;

ALTER TABLE pacientes 
DROP COLUMN IF EXISTS motivo_e_objetivo;

-- ===================================================================
-- 2. ALTERAÇÕES NA TABELA PAGAMENTOS
-- ===================================================================

-- Adicionar campo para indicar se recibo foi emitido via Receita Saúde
ALTER TABLE pagamentos 
ADD COLUMN recibo_receita_saude VARCHAR(10) DEFAULT 'Não'
COMMENT 'Indica se recibo foi emitido via Receita Saúde';

-- Adicionar tipo de pagamento (Particular, Convênio, Clínica)
ALTER TABLE pagamentos 
ADD COLUMN tipo_pagamento VARCHAR(20) DEFAULT 'Particular'
COMMENT 'Tipo de pagamento: Particular, Convênio ou Clínica';

-- Adicionar valor intermediado (quando há convênio/clínica)
ALTER TABLE pagamentos 
ADD COLUMN valor_intermediado DECIMAL(10,2) DEFAULT NULL
COMMENT 'Valor quando há intermediação por convênio ou clínica';

-- Adicionar observações específicas do pagamento
ALTER TABLE pagamentos 
ADD COLUMN observacoes_pagamento TEXT DEFAULT ''
COMMENT 'Observações específicas sobre o pagamento';

-- ===================================================================
-- 3. CRIAR ÍNDICES PARA MELHOR PERFORMANCE
-- ===================================================================

-- Índice para campo de menor/tutelado (consultas frequentes)
CREATE INDEX IF NOT EXISTS idx_pacientes_menor_tutelado ON pacientes(e_menor_tutelado);

-- Índice para tipo de pagamento (relatórios)
CREATE INDEX IF NOT EXISTS idx_pagamentos_tipo ON pagamentos(tipo_pagamento);

-- Índice para recibo receita saúde (relatórios)
CREATE INDEX IF NOT EXISTS idx_pagamentos_receita_saude ON pagamentos(recibo_receita_saude);

-- ===================================================================
-- 4. COMENTÁRIOS FINAIS
-- ===================================================================

-- Script executado com sucesso!
-- Próximos passos: 
-- 1. Atualizar formulários PHP
-- 2. Atualizar functions.php com novas opções
-- 3. Testar funcionalidades

SELECT 'Migration executada com sucesso!' as status;