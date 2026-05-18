#!/bin/bash

echo "🚀 Instalando integração Supabase Storage..."

# Passo 1: Instalar pacote AWS S3
echo ""
echo "📦 Instalando league/flysystem-aws-s3-v3..."
./vendor/bin/sail composer require league/flysystem-aws-s3-v3:^3.0

# Passo 2: Executar migrations
echo ""
echo "🗄️  Executando migrations..."
./vendor/bin/sail artisan migrate

# Passo 3: Testar conexão
echo ""
echo "🧪 Testando conexão com Supabase..."
./vendor/bin/sail artisan tinker << 'EOF'
Storage::disk('supabase')->put('test.txt', 'Hello World', 'public')
Storage::disk('supabase')->url('test.txt')
Storage::disk('supabase')->delete('test.txt')
EOF

echo ""
echo "✅ Instalação concluída!"
echo ""
echo "📝 Próximas etapas:"
echo "1. Verificar se o arquivo 'test.txt' foi criado no Supabase Dashboard"
echo "2. Testar upload com Postman"
echo "3. Consultar documentação em IMPLEMENTACAO_SUPABASE.md"
