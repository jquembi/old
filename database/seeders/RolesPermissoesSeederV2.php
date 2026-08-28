<?php

namespace Database\Seeders;

use App\Models\Permissao;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissoesSeederV2 extends Seeder
{
    /**
     * Versão 2 melhorada com mais papéis e permissões granulares
     */
    public function run(): void
    {
        // ─── INOTU (nível 1) ──────────────────────────────────────
        $chefeSeccao = Role::firstOrCreate(
            ['nome' => Role::CHEFE_SECCAO_TECNICA],
            ['descricao' => 'Chefe de Secção para Área Técnica - Acesso Total', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $chefeSeccaoAdministrativa = Role::firstOrCreate(
            ['nome' => Role::CHEFE_SECCAO_ADMINISTRATIVA],
            ['descricao' => 'Chefe de Secção para Área Administrativa - INOTU', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $secretariaGabinete = Role::firstOrCreate(
            ['nome' => Role::SECRETARIA_GABINETE],
            ['descricao' => 'Secretária do Gabinete do Director - Acesso Total', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $directorInotu = Role::firstOrCreate(
            ['nome' => Role::DIRECTOR_INOTU],
            ['descricao' => 'Director do INOTU - acesso total e gestão da lixeira', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        $tecnicoInotu = Role::firstOrCreate(
            ['nome' => Role::TECNICO_INOTU],
            ['descricao' => 'Técnico do INOTU - Acesso Total', 'grupo' => Role::GRUPO_INOTU, 'nivel' => Role::NIVEL_INOTU]
        );

        // ─── Governo Provincial (nível 2) ──────────────────────────
        $governador = Role::firstOrCreate(
            ['nome' => Role::GOVERNADOR],
            ['descricao' => 'Governador - Liderança Provincial', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        $viceGovernador = Role::firstOrCreate(
            ['nome' => Role::VICE_GOVERNADOR_TECNICO],
            ['descricao' => 'Vice-Governador para Área Técnica - Supervisão', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        $assessor = Role::firstOrCreate(
            ['nome' => Role::ASSESSOR_VICE_GOVERNADOR],
            ['descricao' => 'Assessor do Vice-Governador - Apoio Técnico', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        // Novos papéis provinciais
        $inspectorOrdenamento = Role::firstOrCreate(
            ['nome' => 'inspector_ordenamento'],
            ['descricao' => 'Inspetor de Ordenamento do Território', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        $revisorPareceres = Role::firstOrCreate(
            ['nome' => 'revisor_pareceres_provincial'],
            ['descricao' => 'Revisor de Pareceres Técnicos Provincial', 'grupo' => Role::GRUPO_GOVERNO, 'nivel' => Role::NIVEL_PROVINCIAL]
        );

        // ─── Administração Municipal (nível 3) ──────────────────────
        $adminMunicipal = Role::firstOrCreate(
            ['nome' => Role::ADMIN_MUNICIPAL],
            ['descricao' => 'Administrador Municipal - Gestor Geral', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $adminAdjunto = Role::firstOrCreate(
            ['nome' => Role::ADMIN_MUNICIPAL_ADJUNTO],
            ['descricao' => 'Administrador Municipal Adjunto - Apoio Administrativo', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $directorInfra = Role::firstOrCreate(
            ['nome' => Role::DIRECTOR_INFRAESTRUTURA],
            ['descricao' => 'Director Municipal de Infraestrutura', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        // Novos papéis municipais
        $chefServiços = Role::firstOrCreate(
            ['nome' => 'chefe_servicos_urbanismo'],
            ['descricao' => 'Chefe de Serviços de Ordenamento do Território', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $aprovadorInstrumentos = Role::firstOrCreate(
            ['nome' => 'aprovador_instrumentos'],
            ['descricao' => 'Aprovador de Instrumentos Urbanísticos', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        $revisorOperacoes = Role::firstOrCreate(
            ['nome' => 'revisor_operacoes'],
            ['descricao' => 'Revisor de Operações Urbanísticas', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_MUNICIPAL]
        );

        // ─── Técnicos Municipais (nível 4) ──────────────────────────
        $tecnicoOrdenamento = Role::firstOrCreate(
            ['nome' => Role::TECNICO_ORDENAMENTO],
            ['descricao' => 'Técnicos do Ordenamento do Território', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        // Novos papéis técnicos
        $operadorGis = Role::firstOrCreate(
            ['nome' => 'operador_gis'],
            ['descricao' => 'Operador de Sistemas de Informação Geográfica', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        $tecnicoInstrumental = Role::firstOrCreate(
            ['nome' => 'tecnico_instrumental'],
            ['descricao' => 'Técnico de Instrumentos Urbanísticos', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        $tecnicoLoteamentos = Role::firstOrCreate(
            ['nome' => 'tecnico_loteamentos'],
            ['descricao' => 'Técnico de Loteamentos', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        $tecnicoOperacoes = Role::firstOrCreate(
            ['nome' => 'tecnico_operacoes'],
            ['descricao' => 'Técnico de Operações Urbanísticas', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        $redactorPareceres = Role::firstOrCreate(
            ['nome' => 'redactor_pareceres'],
            ['descricao' => 'Redactor de Pareceres Técnicos', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        $operadorSistema = Role::firstOrCreate(
            ['nome' => 'operador_sistema'],
            ['descricao' => 'Operador de Sistema e Suporte Técnico', 'grupo' => Role::GRUPO_ADMINISTRACAO, 'nivel' => Role::NIVEL_TECNICO]
        );

        // ─── Permissões Expandidas ──────────────────────────────────

        $permissoes = [
            // ==== INSTRUMENTOS URBANÍSTICOS (15 permissões) ====
            ['nome' => 'instrumentos.ver', 'descricao' => 'Ver instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.criar', 'descricao' => 'Criar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.editar_proprio', 'descricao' => 'Editar próprio instrumento', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.editar_municipio', 'descricao' => 'Editar instrumento do município', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.editar_estado', 'descricao' => 'Editar estado do instrumento', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.editar_rascunho', 'descricao' => 'Editar instrumento em rascunho', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.eliminar_proprio', 'descricao' => 'Eliminar próprio instrumento', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.eliminar_municipio', 'descricao' => 'Eliminar instrumento do município', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.eliminar_admin', 'descricao' => 'Eliminar instrumento (admin)', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.aprovar', 'descricao' => 'Aprovar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.rejeitar', 'descricao' => 'Rejeitar instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.submeter', 'descricao' => 'Submeter instrumento para aprovação', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.upload', 'descricao' => 'Upload de ficheiros nos instrumentos', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.gerar_qr', 'descricao' => 'Gerar QR code de instrumento', 'grupo' => 'Instrumentos'],
            ['nome' => 'instrumentos.exportar', 'descricao' => 'Exportar dados de instrumentos', 'grupo' => 'Instrumentos'],

            // ==== LOTEAMENTOS (12 permissões) ====
            ['nome' => 'loteamentos.ver', 'descricao' => 'Ver loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.criar', 'descricao' => 'Criar loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.editar_proprio', 'descricao' => 'Editar próprio loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.editar_municipio', 'descricao' => 'Editar loteamento do município', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.eliminar_proprio', 'descricao' => 'Eliminar próprio loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.eliminar_municipio', 'descricao' => 'Eliminar loteamento do município', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.aprovar', 'descricao' => 'Aprovar loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.rejeitar', 'descricao' => 'Rejeitar loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.submeter', 'descricao' => 'Submeter loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.gerar_qr', 'descricao' => 'Gerar QR code de loteamento', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.exportar', 'descricao' => 'Exportar dados de loteamentos', 'grupo' => 'Loteamentos'],
            ['nome' => 'loteamentos.upload', 'descricao' => 'Upload de ficheiros de loteamentos', 'grupo' => 'Loteamentos'],

            // ==== OPERAÇÕES URBANÍSTICAS (12 permissões) ====
            ['nome' => 'operacoes.ver', 'descricao' => 'Ver operações urbanísticas', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.criar', 'descricao' => 'Criar operações urbanísticas', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.editar_proprio', 'descricao' => 'Editar própria operação', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.editar_municipio', 'descricao' => 'Editar operação do município', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.eliminar_proprio', 'descricao' => 'Eliminar própria operação', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.eliminar_municipio', 'descricao' => 'Eliminar operação do município', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.aprovar', 'descricao' => 'Aprovar operação', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.rejeitar', 'descricao' => 'Rejeitar operação', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.submeter', 'descricao' => 'Submeter operação', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.exportar', 'descricao' => 'Exportar dados de operações', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.upload', 'descricao' => 'Upload de ficheiros de operações', 'grupo' => 'Operações'],
            ['nome' => 'operacoes.revisar', 'descricao' => 'Revisar operação (validação técnica)', 'grupo' => 'Operações'],

            // ==== CROQUIS (12 permissões) ====
            ['nome' => 'croquis.ver', 'descricao' => 'Ver croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.criar', 'descricao' => 'Criar croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.editar_proprio', 'descricao' => 'Editar próprio croqui', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.editar_municipio', 'descricao' => 'Editar croqui do município', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.eliminar_proprio', 'descricao' => 'Eliminar próprio croqui', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.eliminar_municipio', 'descricao' => 'Eliminar croqui do município', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.aprovar', 'descricao' => 'Aprovar croqui', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.rejeitar', 'descricao' => 'Rejeitar croqui', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.submeter', 'descricao' => 'Submeter croqui', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.exportar', 'descricao' => 'Exportar croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.upload', 'descricao' => 'Upload de ficheiros de croquis', 'grupo' => 'Croquis'],
            ['nome' => 'croquis.assinatura_digital', 'descricao' => 'Assinar croqui digitalmente', 'grupo' => 'Croquis'],

            // ==== PARECERES (15 permissões) ====
            ['nome' => 'pareceres.ver', 'descricao' => 'Ver pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.criar', 'descricao' => 'Criar pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.editar_proprio', 'descricao' => 'Editar próprio parecer', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.editar_rascunho', 'descricao' => 'Editar parecer em rascunho', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.eliminar_proprio', 'descricao' => 'Eliminar próprio parecer', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.eliminar_municipio', 'descricao' => 'Eliminar parecer do município', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.submeter', 'descricao' => 'Submeter parecer', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.revisar', 'descricao' => 'Revisar parecer técnico', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.aprovar', 'descricao' => 'Aprovar parecer', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.rejeitar', 'descricao' => 'Rejeitar parecer', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.assinar', 'descricao' => 'Assinar parecer digitalmente', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.exportar', 'descricao' => 'Exportar pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.upload', 'descricao' => 'Upload de ficheiros de pareceres', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.duplicar', 'descricao' => 'Duplicar parecer como baseado', 'grupo' => 'Pareceres'],
            ['nome' => 'pareceres.arquivo', 'descricao' => 'Arquivar parecer', 'grupo' => 'Pareceres'],

            // ==== PROJECTOS SONA (12 permissões) ====
            ['nome' => 'sona.ver', 'descricao' => 'Ver projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.criar', 'descricao' => 'Criar projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.editar_proprio', 'descricao' => 'Editar próprio projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.editar_municipio', 'descricao' => 'Editar projecto SONA do município', 'grupo' => 'SONA'],
            ['nome' => 'sona.eliminar_proprio', 'descricao' => 'Eliminar próprio projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.eliminar_municipio', 'descricao' => 'Eliminar projecto SONA do município', 'grupo' => 'SONA'],
            ['nome' => 'sona.aprovar', 'descricao' => 'Aprovar projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.rejeitar', 'descricao' => 'Rejeitar projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.submeter', 'descricao' => 'Submeter projecto SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.upload', 'descricao' => 'Upload de ficheiros SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.exportar', 'descricao' => 'Exportar projectos SONA', 'grupo' => 'SONA'],
            ['nome' => 'sona.gerar_relatorio', 'descricao' => 'Gerar relatório de SONA', 'grupo' => 'SONA'],

            // ==== OUTROS PROJECTOS (Cartas, Terrenos, Requalificação) - 36 permissões ====
            ['nome' => 'cartas_municipais.ver', 'descricao' => 'Ver cartas municipais', 'grupo' => 'Cartas Municipais'],
            ['nome' => 'cartas_municipais.criar', 'descricao' => 'Criar cartas municipais', 'grupo' => 'Cartas Municipais'],
            ['nome' => 'cartas_municipais.editar', 'descricao' => 'Editar cartas municipais', 'grupo' => 'Cartas Municipais'],
            ['nome' => 'cartas_municipais.eliminar', 'descricao' => 'Eliminar cartas municipais', 'grupo' => 'Cartas Municipais'],
            ['nome' => 'cartas_municipais.aprovar', 'descricao' => 'Aprovar cartas municipais', 'grupo' => 'Cartas Municipais'],
            ['nome' => 'cartas_municipais.exportar', 'descricao' => 'Exportar cartas municipais', 'grupo' => 'Cartas Municipais'],

            ['nome' => 'terrenos_ociosos.ver', 'descricao' => 'Ver terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],
            ['nome' => 'terrenos_ociosos.criar', 'descricao' => 'Criar terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],
            ['nome' => 'terrenos_ociosos.editar', 'descricao' => 'Editar terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],
            ['nome' => 'terrenos_ociosos.eliminar', 'descricao' => 'Eliminar terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],
            ['nome' => 'terrenos_ociosos.aprovar', 'descricao' => 'Aprovar terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],
            ['nome' => 'terrenos_ociosos.exportar', 'descricao' => 'Exportar terrenos ociosos', 'grupo' => 'Terrenos Ociosos'],

            ['nome' => 'requalificacao.ver', 'descricao' => 'Ver projectos de requalificação', 'grupo' => 'Requalificação'],
            ['nome' => 'requalificacao.criar', 'descricao' => 'Criar projectos de requalificação', 'grupo' => 'Requalificação'],
            ['nome' => 'requalificacao.editar', 'descricao' => 'Editar projectos de requalificação', 'grupo' => 'Requalificação'],
            ['nome' => 'requalificacao.eliminar', 'descricao' => 'Eliminar projectos de requalificação', 'grupo' => 'Requalificação'],
            ['nome' => 'requalificacao.aprovar', 'descricao' => 'Aprovar projectos de requalificação', 'grupo' => 'Requalificação'],
            ['nome' => 'requalificacao.exportar', 'descricao' => 'Exportar projectos de requalificação', 'grupo' => 'Requalificação'],

            ['nome' => 'outros_projectos.ver', 'descricao' => 'Ver outros projectos', 'grupo' => 'Outros Projectos'],
            ['nome' => 'outros_projectos.criar', 'descricao' => 'Criar outros projectos', 'grupo' => 'Outros Projectos'],
            ['nome' => 'outros_projectos.editar', 'descricao' => 'Editar outros projectos', 'grupo' => 'Outros Projectos'],
            ['nome' => 'outros_projectos.eliminar', 'descricao' => 'Eliminar outros projectos', 'grupo' => 'Outros Projectos'],
            ['nome' => 'outros_projectos.aprovar', 'descricao' => 'Aprovar outros projectos', 'grupo' => 'Outros Projectos'],
            ['nome' => 'outros_projectos.exportar', 'descricao' => 'Exportar outros projectos', 'grupo' => 'Outros Projectos'],

            ['nome' => 'croquis_elaborados.ver', 'descricao' => 'Ver croquis elaborados', 'grupo' => 'Croquis Elaborados'],
            ['nome' => 'croquis_elaborados.criar', 'descricao' => 'Criar croquis elaborados', 'grupo' => 'Croquis Elaborados'],
            ['nome' => 'croquis_elaborados.editar', 'descricao' => 'Editar croquis elaborados', 'grupo' => 'Croquis Elaborados'],
            ['nome' => 'croquis_elaborados.eliminar', 'descricao' => 'Eliminar croquis elaborados', 'grupo' => 'Croquis Elaborados'],
            ['nome' => 'croquis_elaborados.aprovar', 'descricao' => 'Aprovar croquis elaborados', 'grupo' => 'Croquis Elaborados'],
            ['nome' => 'croquis_elaborados.assinatura', 'descricao' => 'Assinar croquis elaborados', 'grupo' => 'Croquis Elaborados'],

            // ==== PAGAMENTOS RUPE ====
            ['nome' => 'pagamentos.ver', 'descricao' => 'Ver pagamentos RUPE', 'grupo' => 'Pagamentos'],
            ['nome' => 'pagamentos.submeter_comprovativo', 'descricao' => 'Submeter comprovativo de pagamento', 'grupo' => 'Pagamentos'],
            ['nome' => 'pagamentos.gerir_rupe', 'descricao' => 'Gerar e gerir referências RUPE', 'grupo' => 'Pagamentos'],
            ['nome' => 'pagamentos.confirmar', 'descricao' => 'Confirmar ou rejeitar pagamentos', 'grupo' => 'Pagamentos'],

            // ==== AMBIENTE DE TRABALHO (8 permissões) ====
            ['nome' => 'ambiente_trabalho.acessar', 'descricao' => 'Acessar ambiente de trabalho', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.criar_pasta', 'descricao' => 'Criar pasta de trabalho', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.editar_pasta', 'descricao' => 'Editar pasta de trabalho', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.eliminar_pasta', 'descricao' => 'Eliminar pasta de trabalho', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.compartilhar', 'descricao' => 'Compartilhar pasta de trabalho', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.exportar', 'descricao' => 'Exportar dados do ambiente', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.ver_historico', 'descricao' => 'Ver histórico de alterações', 'grupo' => 'Ambiente'],
            ['nome' => 'ambiente_trabalho.restaurar', 'descricao' => 'Restaurar versão anterior', 'grupo' => 'Ambiente'],

            // ==== SIG - EDITOR GEOGRÁFICO (8 permissões) ====
            ['nome' => 'sig_editor.acessar', 'descricao' => 'Acessar editor geográfico', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.editar_camadas', 'descricao' => 'Editar camadas geográficas', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.criar_geometrias', 'descricao' => 'Criar geometrias', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.editar_geometrias_proprias', 'descricao' => 'Editar próprias geometrias', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.editar_geometrias_municipio', 'descricao' => 'Editar geometrias do município', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.eliminar_geometrias', 'descricao' => 'Eliminar geometrias', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.exportar', 'descricao' => 'Exportar dados geográficos', 'grupo' => 'SIG/GIS'],
            ['nome' => 'sig_editor.importar', 'descricao' => 'Importar dados geográficos', 'grupo' => 'SIG/GIS'],

            // ==== GESTÃO DE UTILIZADORES (14 permissões) ====
            ['nome' => 'usuarios.ver', 'descricao' => 'Ver utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.criar', 'descricao' => 'Criar utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.editar_proprio', 'descricao' => 'Editar próprio perfil', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.editar_municipio', 'descricao' => 'Editar utilizadores do município', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.editar_admin', 'descricao' => 'Editar qualquer utilizador (admin)', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.desactivar', 'descricao' => 'Desactivar utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.resetar_senha', 'descricao' => 'Resetar senha de utilizador', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.resetar_pin', 'descricao' => 'Resetar PIN de utilizador', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.atribuir_funcoes', 'descricao' => 'Atribuir funções a utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.remover_funcoes', 'descricao' => 'Remover funções de utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.ativar_autenticacao_dupla', 'descricao' => 'Ativar autenticação dupla', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.ver_auditoria', 'descricao' => 'Ver auditoria de utilizador', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.exportar_lista', 'descricao' => 'Exportar lista de utilizadores', 'grupo' => 'Utilizadores'],
            ['nome' => 'usuarios.importar', 'descricao' => 'Importar utilizadores via ficheiro', 'grupo' => 'Utilizadores'],

            // ==== AUDITORIA E CONFORMIDADE (10 permissões) ====
            ['nome' => 'auditoria.ver_completa', 'descricao' => 'Ver logs de auditoria completos', 'grupo' => 'Auditoria'],
            ['nome' => 'auditoria.ver_limitada', 'descricao' => 'Ver logs de auditoria limitados', 'grupo' => 'Auditoria'],
            ['nome' => 'logs_acesso.ver', 'descricao' => 'Ver logs de acesso', 'grupo' => 'Auditoria'],
            ['nome' => 'logs_permissoes.ver', 'descricao' => 'Ver logs de permissões', 'grupo' => 'Auditoria'],
            ['nome' => 'auditoria.exportar', 'descricao' => 'Exportar relatórios de auditoria', 'grupo' => 'Auditoria'],
            ['nome' => 'auditoria.gerar_relatorio', 'descricao' => 'Gerar relatórios de conformidade', 'grupo' => 'Auditoria'],
            ['nome' => 'auditoria.limpar_logs', 'descricao' => 'Limpar logs antigos (admin)', 'grupo' => 'Auditoria'],
            ['nome' => 'notificacoes_auditoria.receber', 'descricao' => 'Receber notificações de auditoria', 'grupo' => 'Auditoria'],
            ['nome' => 'rastreabilidade.ver_completa', 'descricao' => 'Ver rastreabilidade completa', 'grupo' => 'Auditoria'],
            ['nome' => 'rastreabilidade.exportar', 'descricao' => 'Exportar rastreabilidade', 'grupo' => 'Auditoria'],

            // ==== DASHBOARD E ANÁLISES (12 permissões) ====
            ['nome' => 'dashboard.ver', 'descricao' => 'Ver dashboard geral', 'grupo' => 'Dashboard'],
            ['nome' => 'dashboard.provincial', 'descricao' => 'Ver dashboard provincial', 'grupo' => 'Dashboard'],
            ['nome' => 'dashboard.municipal', 'descricao' => 'Ver dashboard municipal', 'grupo' => 'Dashboard'],
            ['nome' => 'dashboard.tecnico', 'descricao' => 'Ver dashboard técnico', 'grupo' => 'Dashboard'],
            ['nome' => 'analises.acessar', 'descricao' => 'Acessar módulo de análises', 'grupo' => 'Dashboard'],
            ['nome' => 'analises.gerar_relatorio', 'descricao' => 'Gerar relatórios de análise', 'grupo' => 'Dashboard'],
            ['nome' => 'analises.exportar', 'descricao' => 'Exportar análises', 'grupo' => 'Dashboard'],
            ['nome' => 'estatisticas.ver', 'descricao' => 'Ver estatísticas do sistema', 'grupo' => 'Dashboard'],
            ['nome' => 'relatorios.gerar', 'descricao' => 'Gerar relatórios do sistema', 'grupo' => 'Dashboard'],
            ['nome' => 'relatorios.exportar', 'descricao' => 'Exportar relatórios', 'grupo' => 'Dashboard'],
            ['nome' => 'relatorios.agendar', 'descricao' => 'Agendar geração de relatórios', 'grupo' => 'Dashboard'],
            ['nome' => 'graficos.ver_customizado', 'descricao' => 'Ver gráficos customizados', 'grupo' => 'Dashboard'],

            // ==== GESTÃO DO SISTEMA (15 permissões) ====
            ['nome' => 'municipios.gerir_config', 'descricao' => 'Gerir configurações de município', 'grupo' => 'Sistema'],
            ['nome' => 'municipios.ver_dados', 'descricao' => 'Ver dados de município', 'grupo' => 'Sistema'],
            ['nome' => 'municipios.editar', 'descricao' => 'Editar dados de município', 'grupo' => 'Sistema'],
            ['nome' => 'provincias.gerir', 'descricao' => 'Gerir provincias', 'grupo' => 'Sistema'],
            ['nome' => 'roles.ver', 'descricao' => 'Ver funções', 'grupo' => 'Sistema'],
            ['nome' => 'roles.criar', 'descricao' => 'Criar funções', 'grupo' => 'Sistema'],
            ['nome' => 'roles.editar', 'descricao' => 'Editar funções', 'grupo' => 'Sistema'],
            ['nome' => 'permissoes.gerir', 'descricao' => 'Gerir permissões', 'grupo' => 'Sistema'],
            ['nome' => 'backups.gerir', 'descricao' => 'Gerir backups da base de dados', 'grupo' => 'Sistema'],
            ['nome' => 'backups.criar', 'descricao' => 'Criar backup manual', 'grupo' => 'Sistema'],
            ['nome' => 'backups.restaurar', 'descricao' => 'Restaurar de backup', 'grupo' => 'Sistema'],
            ['nome' => 'sistema.limpar_cache', 'descricao' => 'Limpar cache do sistema', 'grupo' => 'Sistema'],
            ['nome' => 'sistema.ver_logs', 'descricao' => 'Ver logs de sistema', 'grupo' => 'Sistema'],
            ['nome' => 'sistema.gerir_configuracoes', 'descricao' => 'Gerir configurações gerais', 'grupo' => 'Sistema'],
            ['nome' => 'sistema.monitorizar', 'descricao' => 'Monitorizar performance do sistema', 'grupo' => 'Sistema'],
            ['nome' => 'lixeira.ver', 'descricao' => 'Aceder à lixeira do sistema', 'grupo' => 'Sistema'],
            ['nome' => 'lixeira.restaurar', 'descricao' => 'Restaurar itens da lixeira', 'grupo' => 'Sistema'],
            ['nome' => 'lixeira.eliminar_definitivo', 'descricao' => 'Eliminar definitivamente itens da lixeira', 'grupo' => 'Sistema'],
        ];

        $permissoesCriadas = [];
        foreach ($permissoes as $p) {
            $permissoesCriadas[$p['nome']] = Permissao::firstOrCreate(
                ['nome' => $p['nome']],
                ['descricao' => $p['descricao'], 'grupo' => $p['grupo']]
            );
        }

        // ─── Atribuir permissões às roles ────────────────────

        // INOTU — acesso total a todos os módulos
        $allPermIds = Permissao::pluck('id')->all();
        $chefeSeccao->permissoes()->syncWithoutDetaching($allPermIds);
        $chefeSeccaoAdministrativa->permissoes()->sync(self::permissoesInformacaoTecnicaIds());
        $secretariaGabinete->permissoes()->syncWithoutDetaching($allPermIds);
        $directorInotu->permissoes()->syncWithoutDetaching($allPermIds);

        // Técnico INOTU — acesso total MENOS gestão de utilizadores, papéis e permissões
        $tecnicoInotuPermissoes = array_filter(
            $permissoesCriadas,
            fn ($p) => !in_array($p->nome, [
                // Remover permissões de gestão de utilizadores (exceto editar próprio perfil)
                'usuarios.editar_municipio',
                'usuarios.editar_admin',
                'usuarios.desactivar',
                'usuarios.resetar_senha',
                'usuarios.resetar_pin',
                'usuarios.atribuir_funcoes',
                'usuarios.remover_funcoes',
                'usuarios.ativar_autenticacao_dupla',
                'usuarios.importar',
                'usuarios.criar',
                // Remover permissões de gestão de papéis
                'roles.criar',
                'roles.editar',
                // Remover permissões de gestão de permissões
                'permissoes.gerir',
            ])
        );
        $tecnicoInotuPermIds = array_map(fn ($p) => $p->id, $tecnicoInotuPermissoes);
        $tecnicoInotu->permissoes()->syncWithoutDetaching($tecnicoInotuPermIds);

        // Governo Provincial — visualização + gestão + revisão
        $provincialPermissoes = [
            // Visualização de tudo
            'instrumentos.ver', 'loteamentos.ver', 'operacoes.ver', 'croquis.ver',
            'pareceres.ver', 'sona.ver', 'cartas_municipais.ver', 'terrenos_ociosos.ver',
            'requalificacao.ver', 'outros_projectos.ver', 'croquis_elaborados.ver',
            // Gestor de utilizadores
            'usuarios.ver', 'usuarios.criar', 'usuarios.editar_municipio', 'usuarios.desactivar',
            'usuarios.resetar_senha', 'usuarios.atribuir_funcoes', 'usuarios.ver_auditoria',
            // Auditoria
            'logs_acesso.ver', 'auditoria.ver_completa', 'logs_permissoes.ver',
            // Dashboard
            'dashboard.ver', 'dashboard.provincial', 'analises.acessar', 'estatisticas.ver',
            'relatorios.gerar', 'relatorios.exportar',
        ];
        $provincialPermIds = collect($provincialPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $governador->permissoes()->syncWithoutDetaching($provincialPermIds);
        $viceGovernador->permissoes()->syncWithoutDetaching($provincialPermIds);
        $assessor->permissoes()->syncWithoutDetaching($provincialPermIds);

        // Inspector de Ordenamento
        $inspectorPermissoes = array_merge($provincialPermissoes, [
            'operacoes.revisar', 'pareceres.revisar', 'instrumentos.aprovar', 'instrumentos.rejeitar',
            'loteamentos.aprovar', 'loteamentos.rejeitar', 'operacoes.aprovar', 'operacoes.rejeitar',
        ]);
        $inspectorPermIds = collect($inspectorPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $inspectorOrdenamento->permissoes()->syncWithoutDetaching($inspectorPermIds);

        // Revisor de Pareceres Provincial
        $revisorPareceresProv = array_merge($provincialPermissoes, [
            'pareceres.revisar', 'pareceres.aprovar', 'pareceres.rejeitar', 'pareceres.exportar',
        ]);
        $revisorPareceresProvIds = collect($revisorPareceresProv)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $revisorPareceres->permissoes()->syncWithoutDetaching($revisorPareceresProvIds);

        // Administração Municipal — completo no município
        $municipalPermissoes = [
            'instrumentos.ver', 'instrumentos.criar', 'instrumentos.editar_municipio',
            'instrumentos.eliminar_municipio', 'instrumentos.aprovar', 'instrumentos.upload',
            'instrumentos.gerar_qr', 'instrumentos.exportar',
            'loteamentos.ver', 'loteamentos.criar', 'loteamentos.editar_municipio',
            'loteamentos.eliminar_municipio', 'loteamentos.aprovar', 'loteamentos.upload',
            'operacoes.ver', 'operacoes.criar', 'operacoes.editar_municipio',
            'operacoes.eliminar_municipio', 'operacoes.aprovar', 'operacoes.upload',
            'croquis.ver', 'croquis.criar', 'croquis.editar_municipio',
            'croquis.eliminar_municipio', 'croquis.upload',
            'pareceres.ver', 'pareceres.criar', 'pareceres.upload',
            'sona.ver', 'sona.criar', 'sona.editar_municipio', 'sona.eliminar_municipio',
            'sona.upload', 'sona.exportar',
            'cartas_municipais.ver', 'cartas_municipais.criar', 'cartas_municipais.editar',
            'cartas_municipais.eliminar', 'cartas_municipais.exportar',
            'terrenos_ociosos.ver', 'terrenos_ociosos.criar', 'terrenos_ociosos.editar',
            'terrenos_ociosos.eliminar', 'terrenos_ociosos.exportar',
            'requalificacao.ver', 'requalificacao.criar', 'requalificacao.editar',
            'requalificacao.eliminar', 'requalificacao.exportar',
            'outros_projectos.ver', 'outros_projectos.criar', 'outros_projectos.editar',
            'outros_projectos.eliminar', 'outros_projectos.exportar',
            'croquis_elaborados.ver', 'croquis_elaborados.criar', 'croquis_elaborados.editar',
            'croquis_elaborados.eliminar', 'croquis_elaborados.assinatura',
            'pagamentos.ver', 'pagamentos.submeter_comprovativo',
            'usuarios.ver', 'usuarios.criar', 'usuarios.editar_municipio',
            'usuarios.desactivar', 'usuarios.ver_auditoria',
            'ambiente_trabalho.acessar', 'ambiente_trabalho.criar_pasta',
            'ambiente_trabalho.editar_pasta', 'ambiente_trabalho.compartilhar',
            'sig_editor.acessar', 'sig_editor.editar_camadas',
            'sig_editor.criar_geometrias', 'sig_editor.editar_geometrias_municipio',
            'sig_editor.exportar', 'sig_editor.importar',
            'dashboard.ver', 'dashboard.municipal', 'analises.acessar',
            'estatisticas.ver', 'relatorios.gerar', 'relatorios.exportar',
            'auditoria.ver_limitada', 'logs_acesso.ver',
            'municipios.ver_dados', 'municipios.gerir_config',
        ];
        $municipalPermIds = collect($municipalPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $adminMunicipal->permissoes()->sync(self::permissoesInformacaoTecnicaIds());
        $adminAdjunto->permissoes()->sync(self::permissoesInformacaoTecnicaIds());
        $directorInfra->permissoes()->syncWithoutDetaching($municipalPermIds);

        // Chefe de Serviços de Urbanismo
        $chefServiçosPermissoes = array_merge($municipalPermissoes, [
            'instrumentos.aprovar', 'instrumentos.rejeitar', 'instrumentos.editar_estado',
            'loteamentos.aprovar', 'loteamentos.rejeitar',
            'operacoes.aprovar', 'operacoes.rejeitar', 'operacoes.revisar',
            'pareceres.revisar', 'pareceres.aprovar', 'pareceres.rejeitar',
        ]);
        $chefServiçosPermIds = collect($chefServiçosPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $chefServiços->permissoes()->syncWithoutDetaching($chefServiçosPermIds);

        // Aprovador de Instrumentos
        $aprovadorPermissoes = collect($municipalPermissoes)->filter(fn ($n) =>
            !str_contains($n, 'usuarios') && !str_contains($n, 'municipios')
        )->values()->all();
        $aprovadorPermissoes = array_merge($aprovadorPermissoes, [
            'instrumentos.aprovar', 'instrumentos.rejeitar', 'instrumentos.editar_estado',
        ]);
        $aprovadorPermIds = collect($aprovadorPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $aprovadorInstrumentos->permissoes()->syncWithoutDetaching($aprovadorPermIds);

        // Revisor de Operações
        $revisorOpsPermissoes = collect(array_merge(
            array_filter($municipalPermissoes, fn ($n) => str_contains($n, 'operacoes')),
            array_filter($municipalPermissoes, fn ($n) => str_contains($n, 'croquis')),
            ['dashboard.municipal', 'auditoria.ver_limitada']
        ))->values()->all();
        $revisorOpsPermissoes[] = 'operacoes.revisar';
        $revisorOpsPermIds = collect($revisorOpsPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $revisorOperacoes->permissoes()->syncWithoutDetaching($revisorOpsPermIds);

        // Técnicos — trabalho operacional
        $tecnicoPermissoes = [
            'instrumentos.ver', 'instrumentos.criar', 'instrumentos.editar_proprio',
            'instrumentos.submeter', 'instrumentos.upload',
            'loteamentos.ver', 'loteamentos.criar', 'loteamentos.editar_proprio',
            'loteamentos.submeter', 'loteamentos.upload',
            'operacoes.ver', 'operacoes.criar', 'operacoes.editar_proprio',
            'operacoes.submeter', 'operacoes.upload',
            'croquis.ver', 'croquis.criar', 'croquis.editar_proprio',
            'croquis.submeter', 'croquis.upload', 'croquis.assinatura_digital',
            'pareceres.ver', 'pareceres.criar', 'pareceres.editar_proprio',
            'pareceres.submeter', 'pareceres.upload',
            'sona.ver', 'sona.criar', 'sona.editar_proprio', 'sona.submeter',
            'sona.upload',
            'cartas_municipais.ver', 'terrenos_ociosos.ver', 'requalificacao.ver',
            'outros_projectos.ver', 'croquis_elaborados.ver',
            'ambiente_trabalho.acessar', 'ambiente_trabalho.criar_pasta',
            'ambiente_trabalho.editar_pasta',
            'sig_editor.acessar', 'sig_editor.criar_geometrias',
            'sig_editor.editar_geometrias_proprias', 'sig_editor.exportar',
            'dashboard.municipal', 'usuarios.editar_proprio',
        ];
        $tecnicoPermIds = collect($tecnicoPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $tecnicoOrdenamento->permissoes()->syncWithoutDetaching($tecnicoPermIds);

        // Operador GIS
        $operadorGisPermissoes = [
            'sig_editor.acessar', 'sig_editor.editar_camadas', 'sig_editor.criar_geometrias',
            'sig_editor.editar_geometrias_municipio', 'sig_editor.eliminar_geometrias',
            'sig_editor.exportar', 'sig_editor.importar',
            'instrumentos.ver', 'loteamentos.ver', 'operacoes.ver', 'croquis.ver',
            'ambiente_trabalho.acessar', 'dashboard.municipal',
            'relatorios.gerar', 'relatorios.exportar',
        ];
        $operadorGisPermIds = collect($operadorGisPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $operadorGis->permissoes()->syncWithoutDetaching($operadorGisPermIds);

        // Técnico Instrumental
        $tecnicoInstPermissoes = array_merge($tecnicoPermissoes, [
            'instrumentos.editar_municipio', 'instrumentos.editar_estado',
            'instrumentos.gerar_qr', 'instrumentos.exportar',
        ]);
        $tecnicoInstPermIds = collect($tecnicoInstPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $tecnicoInstrumental->permissoes()->syncWithoutDetaching($tecnicoInstPermIds);

        // Técnico Loteamentos
        $tecnicoLotePermissoes = array_merge($tecnicoPermissoes, [
            'loteamentos.editar_municipio', 'loteamentos.gerar_qr', 'loteamentos.exportar',
        ]);
        $tecnicoLotePermIds = collect($tecnicoLotePermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $tecnicoLoteamentos->permissoes()->syncWithoutDetaching($tecnicoLotePermIds);

        // Técnico Operações
        $tecnicoOpsPermissoes = array_merge($tecnicoPermissoes, [
            'operacoes.editar_municipio', 'operacoes.exportar', 'operacoes.revisar',
        ]);
        $tecnicoOpsPermIds = collect($tecnicoOpsPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $tecnicoOperacoes->permissoes()->syncWithoutDetaching($tecnicoOpsPermIds);

        // Redactor de Pareceres
        $redactorPermissoes = [
            'pareceres.ver', 'pareceres.criar', 'pareceres.editar_proprio',
            'pareceres.editar_rascunho', 'pareceres.submeter', 'pareceres.upload',
            'pareceres.exportar', 'pareceres.duplicar', 'pareceres.arquivo',
            'instrumentos.ver', 'loteamentos.ver', 'operacoes.ver',
            'dashboard.municipal', 'ambiente_trabalho.acessar',
            'usuarios.editar_proprio',
        ];
        $redactorPermIds = collect($redactorPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $redactorPareceres->permissoes()->syncWithoutDetaching($redactorPermIds);

        // Operador de Sistema
        $operadorSistPermissoes = [
            'sistema.ver_logs', 'sistema.limpar_cache', 'sistema.monitorizar',
            'backups.criar', 'backups.gerir', 'backups.restaurar',
            'auditoria.ver_completa', 'logs_acesso.ver', 'logs_permissoes.ver',
            'usuarios.ver', 'dashboard.ver', 'dashboard.tecnico',
            'dashboard.provincial', 'dashboard.municipal',
            'usuarios.editar_proprio',
        ];
        $operadorSistPermIds = collect($operadorSistPermissoes)->map(fn ($n) => $permissoesCriadas[$n]->id)->toArray();
        $operadorSistema->permissoes()->syncWithoutDetaching($operadorSistPermIds);
    }

    private static function permissoesInformacaoTecnicaIds(): array
    {
        return Permissao::query()
            ->get()
            ->filter(fn (Permissao $permissao) => self::isPermissaoInformacaoTecnica($permissao->nome))
            ->map(fn (Permissao $permissao) => $permissao->id)
            ->values()
            ->all();
    }

    private static function isPermissaoInformacaoTecnica(string $permissao): bool
    {
        $permissoesFixas = [
            'dashboard.ver',
            'dashboard.municipal',
            'dashboard.tecnico',
            'analises.acessar',
            'estatisticas.ver',
            'relatorios.gerar',
            'relatorios.exportar',
            'municipios.ver_dados',
            'sistema.ver_logs',
            'sistema.monitorizar',
            'documentos.ver',
            'documentos.download',
        ];

        if (in_array($permissao, $permissoesFixas, true)) {
            return true;
        }

        [$modulo, $accao] = array_pad(explode('.', $permissao, 2), 2, '');

        $modulosTecnicos = [
            'instrumentos',
            'loteamentos',
            'operacoes',
            'croquis',
            'pareceres',
            'sona',
            'cartas_municipais',
            'terrenos_ociosos',
            'requalificacao',
            'outros_projectos',
            'croquis_elaborados',
            'ambiente_trabalho',
            'sig_editor',
            'documentos',
        ];

        if (! in_array($modulo, $modulosTecnicos, true)) {
            return false;
        }

        return $accao === 'ver'
            || str_starts_with($accao, 'ver_')
            || in_array($accao, ['acessar', 'download', 'exportar'], true);
    }
}
