<?php

use App\Http\Controllers\Auth\usuarioController;
use App\Http\Controllers\contabilidade\contabilidadeController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\fechamento\fechamentoController;
use App\Http\Controllers\FormacaoPreco\FormacaoPrecoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\menu\menuController;
use App\Http\Controllers\produtoCusto\ProdutoCustoMpController;
use App\Http\Controllers\singular\comissaoController;
use App\Http\Controllers\singular\pagarController;
use App\Http\Controllers\singular\receberController;
use App\Http\Controllers\singular\vendasController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['logout' => false]);

Route::get('/', function () {
    return view('auth.login');
});

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    Route::get('/public', [HomeController::class, 'index']);
    Route::post('/logout', [HomeController::class, 'logout'])->name('logout');
    Route::post('/trocaBase', [HomeController::class, 'trocaBase'])->name('apontamento.trocaBase');

    /********************************** Dashboard ***************************************************************/
    Route::group(['namespace' => 'dashboard'], function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/dados', [DashboardController::class, 'dados'])->name('dashboard.dados');
    });

    /********************************** menu ***************************************************************/
    Route::group(['namespace' => 'menu'], function () {
        Route::get('menu', [menuController::class, 'listAllmenu'])->name('menu.listAll');
        Route::get('menu/novo', [menuController::class, 'formAddmenu'])->name('menu.formAddmenu');
        Route::get('menu/editar/{menu}', [menuController::class, 'formEditmenu'])->name('menu.formEditmenu');
        Route::post('menu/store', [menuController::class, 'stroremenu'])->name('menu.store');
        Route::patch('menu/edit/{menu}', [menuController::class, 'edit'])->name('menu.edit');
        Route::delete('menu/destroy/{menu}', [menuController::class, 'destroy'])->name('menu.destroy');

        Route::get('menu/menuUsuario', [menuController::class, 'menuUsuario'])->name('menu.menuUsuario');
        Route::post('menu/disponivel', [menuController::class, 'disponivel'])->name('menu.disponivel');
        Route::post('menu/menuLiberado', [menuController::class, 'menuLiberado'])->name('menu.menuLiberado');
        Route::post('menu/addMenuUsuario', [menuController::class, 'addMenuUsuario'])->name('menu.addMenuUsuario');
        Route::post('menu/removeMenuUsuario', [menuController::class, 'removeMenuUsuario'])->name('menu.removeMenuUsuario');
    });

    /********************************** usuario ***************************************************************/
    Route::group(['namespace' => 'usuario'], function () {
        Route::post('usuario/updateSenha', [usuarioController::class, 'updateSenha'])->name('usuario.updateSenha');
        Route::get('usuario/base', [usuarioController::class, 'baseListAll'])->name('usuario.baseListAll');
        Route::get('usuario/baseNovo', [usuarioController::class, 'baseNovo'])->name('usuario.baseNovo');
        Route::post('usuario/baseStore', [usuarioController::class, 'baseStore'])->name('usuario.baseStore');
        Route::get('usuario/baseEditar/{id}', [usuarioController::class, 'baseEditar'])->name('usuario.baseEditar');
        Route::patch('usuario/baseEdit/{id}', [usuarioController::class, 'baseEdit'])->name('usuario.baseEdit');

        Route::get('usuario/usuarioBase', [usuarioController::class, 'usuarioBase'])->name('usuario.usuarioBase');
        Route::get('usuario/usuarioBaseNovo', [usuarioController::class, 'usuarioBaseNovo'])->name('usuario.usuarioBaseNovo');
        Route::post('usuario/usuarioBaseStore', [usuarioController::class, 'usuarioBaseStore'])->name('usuario.usuarioBaseStore');
        Route::get('usuario/usuarioBaseEditar/{id}', [usuarioController::class, 'usuarioBaseEditar'])->name('usuario.usuarioBaseEditar');
        Route::patch('usuario/usuarioBaseEdit/{id}', [usuarioController::class, 'usuarioBaseEdit'])->name('usuario.usuarioBaseEdit');
    });

    /********************************** vendas ***************************************************************/
    Route::group(['namespace' => 'vendas'], function () {
        Route::get('vendas', [vendasController::class, 'listAll'])->name('vendas.listAll');
        Route::get('vendas/papeleta', [vendasController::class, 'papeleta'])->name('vendas.papeleta');
        Route::post('vendas/papeletaDados', [vendasController::class, 'papeletaDados'])->name('vendas.papeletaDados');
        Route::post('vendas/atualiza_papeleta', [vendasController::class, 'atualiza_papeleta'])->name('vendas.atualiza_papeleta');
        Route::post('vendas/atualiza_papeleta_frete', [vendasController::class, 'atualiza_papeleta_frete'])->name('vendas.atualiza_papeleta_frete');
        Route::post('vendas/atualiza_papeleta_parcela', [vendasController::class, 'atualiza_papeleta_parcela'])->name('vendas.atualiza_papeleta_parcela');
        Route::get('vendas/cargas_pdf/{carga}', [vendasController::class, 'cargas_pdf'])->name('vendas.cargas_pdf');
    });

    /********************************** comissao ***************************************************************/
    Route::group(['namespace' => 'comissao'], function () {
        Route::get('comissao', [comissaoController::class, 'listAll'])->name('comissao.listAll');
        Route::post('comissao/alteraBase', [comissaoController::class, 'alteraBase'])->name('comissao.alteraBase');
        Route::get('comissao/comissaoPagar', [comissaoController::class, 'comissaoPagar'])->name('comissao.comissaoPagar');
        Route::get('comissao/imprimirComissaoPagar', [comissaoController::class, 'imprimirComissaoPagar'])->name('receber.imprimirComissaoPagar');
    });

    /********************************** Pagar ***************************************************************/
    Route::group(['namespace' => 'pagar'], function () {
        Route::get('pagar', [pagarController::class, 'listAll'])->name('pagar.listAll');
    });

    /********************************** Receber ***************************************************************/
    Route::group(['namespace' => 'receber'], function () {
        Route::get('receber', [receberController::class, 'listAll'])->name('receber.listAll');
        Route::get('receber/imprimir', [receberController::class, 'imprimir'])->name('receber.imprimir');
    });

    /********************************** contabilidade ***************************************************************/
    Route::group(['namespace' => 'contabilidade'], function () {
        Route::get('contabilidade/exportaBaixas', [contabilidadeController::class, 'exportaBaixas'])->name('contabilidade.exportaBaixas');
        Route::post('contabilidade/geraArquivoBiaxas', [contabilidadeController::class, 'geraArquivoBiaxas'])->name('contabilidade.geraArquivoBiaxas');
        Route::get('contabilidade/download/{arq}', [contabilidadeController::class, 'download'])->name('contabilidade.download');

        Route::get('contabilidade/estoque', [contabilidadeController::class, 'estoque'])->name('contabilidade.estoque');
        Route::post('contabilidade/geraEstoque', [contabilidadeController::class, 'geraEstoque'])->name('contabilidade.geraEstoque');
        Route::get('contabilidade/importaAcertoEstoque', [contabilidadeController::class, 'importaAcertoEstoque'])->name('contabilidade.importaAcertoEstoque');
        Route::post('contabilidade/geraAcertoEstoque', [contabilidadeController::class, 'geraAcertoEstoque'])->name('contabilidade.geraAcertoEstoque');

        Route::get('contabilidade/sped', [contabilidadeController::class, 'sped'])->name('contabilidade.sped');
        Route::post('contabilidade/geraSped', [contabilidadeController::class, 'geraSped'])->name('contabilidade.geraSped');
    });

    /********************************** Fechamento ***************************************************************/
    Route::group(['namespace' => 'fechamento'], function () {
        Route::get('fechamento', [fechamentoController::class, 'listAll'])->name('fechamento.listAll');
        Route::get('fechamento/fechamento', [fechamentoController::class, 'fechamento'])->name('fechamento.fechamento');
        Route::get('fechamento/produtoComFicha', [fechamentoController::class, 'produtoComFicha'])->name('fechamento.produtoComFicha');
        Route::post('fechamento/consutaProdutoComFicha', [fechamentoController::class, 'consutaProdutoComFicha'])->name('fechamento.consutaProdutoComFicha');
        Route::get('fechamento/importaHorasMlc', [fechamentoController::class, 'importaHorasMlc'])->name('fechamento.importaHorasMlc');
        Route::post('fechamento/gravaHorasMLC', [fechamentoController::class, 'gravaHorasMLC'])->name('fechamento.gravaHorasMLC');
    });

    /********************************** ProdutoCusto ***************************************************************/
    Route::group(['namespace' => 'ProdutoCusto'], function () {
        Route::get('custosMp', [ProdutoCustoMpController::class, 'listAll'])->name('custosMp.listAll');
        Route::post('custosMp/salvar-manual', [ProdutoCustoMpController::class, 'salvarManual'])->name('custosMp.salvar-manual');
        Route::post('custosMp/importar-manual-lote', [ProdutoCustoMpController::class, 'importarManualLote'])->name('custosMp.importar-manual-lote');
    });

    /********************************** FormacaoPreco ***************************************************************/
    Route::group(['namespace' => 'FormacaoPreco'], function () {
        Route::get('formacaoPreco', [FormacaoPrecoController::class, 'listAll'])->name('formacaoPreco.listAll');
        Route::get('formacaoPreco/formacaoPreco', [FormacaoPrecoController::class, 'formacaoPreco'])->name('formacaoPreco.formacaoPreco');
        Route::get('formacaoPreco/buscar-custo-produto', [FormacaoPrecoController::class, 'buscarCustoProduto'])->name('formacaoPreco.buscarCustoProduto');
        Route::post('formacaoPreco/salvar', [FormacaoPrecoController::class, 'salvarFormacaoPreco'])->name('formacaoPreco.salvar');
        Route::post('formacaoPreco/gravar-parametros-padrao', [FormacaoPrecoController::class, 'gravarParametrosPadrao'])->name('formacaoPreco.gravarParametrosPadrao');
    });
});
