$ErrorActionPreference = 'Stop'
$projeto = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location -LiteralPath $projeto
Write-Host 'BibliotecaORM - Avaliacao Pratica da Aula 11' -ForegroundColor Cyan
Write-Host 'Senha solicitada somente neste notebook (ela nao sera salva em arquivo).'
$usuario = Read-Host 'Usuario MySQL (pressione Enter para root)'
if ([string]::IsNullOrWhiteSpace($usuario)) { $usuario = 'root' }
$senhaSegura = Read-Host 'Senha do MySQL' -AsSecureString
$ponteiro = [IntPtr]::Zero
$ponteiro = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($senhaSegura)
try {
    $env:DB_PASS = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ponteiro)
    $env:DB_HOST = '127.0.0.1'
    $env:DB_USER = $usuario
    $php = (Get-Command php.exe -ErrorAction Stop).Source
    $ext = Join-Path (Split-Path $php -Parent) 'ext'
    $opcoes = @('-d', "extension_dir=$ext", '-d', 'extension=pdo_mysql')
    if (-not (& $php @opcoes -m | Select-String -Pattern '^pdo_mysql$' -Quiet)) {
        throw 'Driver PHP pdo_mysql nao foi carregado.'
    }
    $pastaEvidencias = Join-Path $projeto 'evidencias'
    New-Item -ItemType Directory -Path $pastaEvidencias -Force | Out-Null
    $arquivoEvidencia = Join-Path $pastaEvidencias ('execucao-' + (Get-Date -Format 'yyyyMMdd-HHmmss') + '.txt')
    Set-Content -LiteralPath $arquivoEvidencia -Value ('BibliotecaORM - Aula 11 | ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss')) -Encoding UTF8
    Add-Content -LiteralPath $arquivoEvidencia -Value 'PHP, PDO, MySQL | saida real do projeto, sem credenciais' -Encoding UTF8
    function Rodar([string]$etapa, [string]$script, [string]$banco) {
        $env:DB_NAME = $banco
        Write-Host "`n==== $etapa ====" -ForegroundColor Cyan
        Add-Content -LiteralPath $arquivoEvidencia -Value "`n==== $etapa ====" -Encoding UTF8
        $resultado = & $php @opcoes $script 2>&1
        $codigo = $LASTEXITCODE
        foreach ($linha in $resultado) {
            $texto = [string]$linha
            Write-Host $texto
            Add-Content -LiteralPath $arquivoEvidencia -Value $texto -Encoding UTF8
        }
        if ($codigo -ne 0) { throw "Falha na etapa: $etapa (codigo $codigo)" }
    }
    Rodar 'Criacao dos bancos' '.\preparar_bancos.php' 'biblioteca_orm'
    Rodar '5 testes de dominio' '.\tests\domain.php' 'biblioteca_orm_teste'
    Rodar '13 testes de integracao' '.\tests\smoke.php' 'biblioteca_orm_teste'
    Rodar '7 evidencias da demonstracao' '.\demo.php' 'biblioteca_orm'
    Write-Host "`nSUCESSO: todos os passos foram executados." -ForegroundColor Green
    Write-Host "Evidencia salva em: $arquivoEvidencia" -ForegroundColor Green
} catch {
    Write-Host "FALHA: $($_.Exception.Message)" -ForegroundColor Red
} finally {
    if ($ponteiro -ne [IntPtr]::Zero) { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ponteiro) }
    Remove-Item Env:\DB_PASS,Env:\DB_USER,Env:\DB_NAME,Env:\DB_HOST -ErrorAction SilentlyContinue
    [void](Read-Host 'Pressione Enter para fechar')
}
