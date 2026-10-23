<div class="modal fade" id="ModalSenha" tabindex="-1" role="dialog" aria-labelledby="ModalSenhaLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="panel panel-primary">
                <div class="panel-heading bg-primary text-white">
                    <div class="">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="ModalInsercaoLabel">Trocar Senha:</h4>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger"  id="alert">
                    Senhas não conferem!!!
                </div>
                <form>
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-12">
                            Nova Senha:
                            <input type="password" class="form-control" id="novaSenha" name="novaSenha">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            Confirma Senha:
                            <input type="password" class="form-control" id="confirmaSenha" name="confirmaSenha">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="form-group col-md-4">
                    <button type="button" class="btn btn-sm btn-success btn-block" id="alteraSenha" data-dismiss="modal" disabled><i class="fa fa-check"></i> Alterar</button>
                </div>
                <div class="form-group col-md-4">
                    <button type="button" class="btn  btn-sm btn-danger btn-block" id="" data-dismiss="modal"><i class="fa fa-times"></i>  Cancela</button>
                </div>
            </div>
        </div>
    </div>
</div>

