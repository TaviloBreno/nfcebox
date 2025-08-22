# Impressão Local do DANFE

Este documento explica como configurar e utilizar a impressão local do DANFE (Documento Auxiliar da Nota Fiscal Eletrônica) a partir do PDF gerado pelo sistema.

## Métodos de Impressão Local

### 1. Impressão via Navegador (Mais Simples)

1. **Acesse a NFC-e**: Vá para a tela de gerenciamento de NFC-e (`/nfce`)
2. **Reimprimir**: Clique no botão "Reimprimir" (ícone de impressora) da NFC-e desejada
3. **Imprimir**: O PDF será aberto no navegador. Use `Ctrl+P` ou clique em "Imprimir"
4. **Configurar**: Selecione sua impressora térmica e ajuste as configurações:
   - **Tamanho do papel**: Personalizado (58mm x variável)
   - **Margens**: Mínimas (0mm)
   - **Orientação**: Retrato
   - **Escala**: 100%

### 2. Download e Impressão via Sistema Operacional

#### Windows

1. **Baixar PDF**: Clique no botão "Baixar PDF" na lista de NFC-e
2. **Abrir arquivo**: Localize o arquivo baixado e abra com o Adobe Reader ou visualizador padrão
3. **Imprimir**: Use `Ctrl+P` ou menu "Arquivo > Imprimir"
4. **Configurações recomendadas**:
   - Impressora: Selecione sua impressora térmica
   - Tamanho: Personalizado (58mm de largura)
   - Ajustar à página: Não
   - Tamanho real: Sim
   - Orientação: Retrato

#### Linux

```bash
# Instalar CUPS (se não estiver instalado)
sudo apt-get install cups cups-client

# Listar impressoras disponíveis
lpstat -p

# Imprimir arquivo PDF
lp -d nome_da_impressora -o media=Custom.58x200mm arquivo.pdf

# Ou usando lpr
lpr -P nome_da_impressora -o media=Custom.58x200mm arquivo.pdf
```

#### macOS

```bash
# Listar impressoras
lpstat -p

# Imprimir com configurações específicas
lp -d nome_da_impressora -o media=Custom.58x200mm -o fit-to-page arquivo.pdf
```

## Configuração de Impressoras Térmicas

### Impressoras Suportadas

- **Bematech**: MP-4200 TH, MP-5000 TH
- **Epson**: TM-T20, TM-T88V, TM-T88VI
- **Zebra**: ZD220, ZD230
- **Elgin**: i9, i8
- **Daruma**: DR700, DR800

### Configuração no Windows

1. **Instalar Driver**: Baixe e instale o driver específico da sua impressora
2. **Adicionar Impressora**: 
   - Painel de Controle > Dispositivos e Impressoras
   - Adicionar Impressora > Adicionar impressora local
   - Selecionar porta (USB, Serial ou Rede)
3. **Configurar Papel**:
   - Propriedades da Impressora > Preferências
   - Tamanho do papel: Personalizado (58mm x 200mm)
   - Qualidade: Rascunho ou Normal

### Configuração no Linux (CUPS)

```bash
# Acessar interface web do CUPS
http://localhost:631

# Ou via linha de comando
sudo lpadmin -p impressora_termica -E -v usb://Bematech/MP-4200%20TH \
  -m drv:///sample.drv/generic.ppd

# Definir como padrão
sudo lpadmin -d impressora_termica

# Configurar tamanho do papel
sudo lpadmin -p impressora_termica -o media=Custom.58x200mm
```

## Configurações Avançadas

### Ajuste de Densidade

Para impressoras térmicas, você pode ajustar a densidade de impressão:

```bash
# Linux - ajustar densidade (0-15)
lp -d impressora_termica -o density=10 arquivo.pdf

# Windows - via propriedades da impressora
# Propriedades > Avançado > Densidade de Impressão
```

### Velocidade de Impressão

```bash
# Linux - ajustar velocidade
lp -d impressora_termica -o speed=medium arquivo.pdf

# Opções: slow, medium, fast
```

### Corte Automático

```bash
# Linux - habilitar corte automático
lp -d impressora_termica -o cut=auto arquivo.pdf
```

## Impressão em Lote

### Script para Windows (PowerShell)

```powershell
# imprimir_lote.ps1
param(
    [string]$PastaArquivos,
    [string]$NomeImpressora
)

Get-ChildItem -Path $PastaArquivos -Filter "*.pdf" | ForEach-Object {
    Start-Process -FilePath "AcroRd32.exe" -ArgumentList "/t", $_.FullName, $NomeImpressora -Wait
    Write-Host "Impresso: $($_.Name)"
}
```

### Script para Linux (Bash)

```bash
#!/bin/bash
# imprimir_lote.sh

PASTA_ARQUIVOS="$1"
IMPRESSORA="$2"

for arquivo in "$PASTA_ARQUIVOS"/*.pdf; do
    if [ -f "$arquivo" ]; then
        lp -d "$IMPRESSORA" -o media=Custom.58x200mm "$arquivo"
        echo "Impresso: $(basename "$arquivo")"
        sleep 2  # Pausa entre impressões
    fi
done
```

## Solução de Problemas

### Problemas Comuns

1. **PDF não imprime corretamente**:
   - Verifique se o tamanho do papel está configurado como 58mm
   - Desabilite "Ajustar à página"
   - Use "Tamanho real" ou "100%"

2. **Texto cortado nas laterais**:
   - Reduza as margens para 0mm
   - Verifique se a largura está configurada corretamente

3. **Impressão muito clara**:
   - Aumente a densidade de impressão
   - Verifique se o papel térmico não está vencido

4. **Impressora não responde**:
   - Verifique conexão USB/Serial/Rede
   - Reinicie o spooler de impressão
   - Teste com outro documento

### Comandos de Diagnóstico

```bash
# Linux - verificar status da impressora
lpstat -p impressora_termica

# Verificar fila de impressão
lpq -P impressora_termica

# Limpar fila de impressão
lprm -P impressora_termica -

# Testar impressora
echo "Teste de impressão" | lp -d impressora_termica
```

```cmd
REM Windows - verificar impressoras
wmic printer list brief

REM Verificar fila de impressão
print /?

REM Limpar fila
net stop spooler
net start spooler
```

## Integração com Sistema

### API para Impressão Automática

O sistema oferece um endpoint para impressão em rede:

```javascript
// Exemplo de uso via JavaScript
fetch('/nfce/123/print-network', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        printer_ip: '192.168.1.100',
        printer_port: 9100
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Impresso com sucesso!');
    }
});
```

### Configuração de Impressora de Rede

1. **Encontrar IP da impressora**:
   - Imprimir página de configuração
   - Verificar no painel da impressora
   - Usar comando `ping` para testar conectividade

2. **Configurar porta RAW**:
   - Porta padrão: 9100
   - Protocolo: RAW/TCP
   - Timeout: 30 segundos

3. **Testar conexão**:
   ```bash
   # Linux/macOS
   telnet 192.168.1.100 9100
   
   # Windows
   Test-NetConnection -ComputerName 192.168.1.100 -Port 9100
   ```

## Manutenção

### Limpeza Regular

- **Cabeça de impressão**: Use álcool isopropílico e cotonete
- **Sensor de papel**: Limpe com pano seco
- **Mecanismo de corte**: Remova resíduos de papel

### Substituição de Consumíveis

- **Papel térmico**: Use apenas papel de qualidade (58mm x 40mm)
- **Fita**: Para impressoras de transferência térmica

### Backup de Configurações

```bash
# Linux - backup configurações CUPS
sudo cp -r /etc/cups /backup/cups-$(date +%Y%m%d)

# Restaurar configurações
sudo cp -r /backup/cups-20240101 /etc/cups
sudo systemctl restart cups
```

---

**Suporte Técnico**: Para problemas específicos, consulte a documentação do fabricante da sua impressora ou entre em contato com o suporte técnico do sistema.