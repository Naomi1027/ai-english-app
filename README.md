<p align="center">
<img src="path/to/your/app-logo.png" width="400" alt="AI英会話アプリロゴ">
</p>

# AI英会話アプリ

AIを活用したオンライン英会話学習プラットフォームです。ユーザーは時間や場所を問わず、AIと英会話の練習ができます。

## 主な機能

- 🤖 AIとのリアルタイム会話
- 📝 会話履歴の保存と振り返り
- 🎯 レベル別学習コース
- 📊 学習進捗の可視化
- 🔍 発音・文法チェック機能

## 開発環境のセットアップ

### 必要要件

- PHP 8.1以上
- Composer
- Node.js 16以上
- MySQL 8.0以上
- Laravel 10.x

### インストール手順

1. リポジトリのクローン
```bash
git clone https://github.com/yourusername/ai-english-conversation.git
```

2. 依存パッケージのインストール
```bash
composer install
npm install
```

3. 環境設定
```bash
cp .env.example .env
php artisan key:generate
```

4. マイグレーションの実行
```bash
php artisan migrate
```

5. 開発サーバーの起動
```bash
php artisan serve
npm run dev
```

これで http://localhost にアクセスできます。

## API設定

OpenAI APIを使用するために、.envファイルに以下の設定を追加してください：

```env
OPENAI_API_KEY=your_api_key
```

## デプロイ

- Laravel Cloud
  - postgres

## 技術スタック

- フロントエンド
  - inertia.js
  - TailwindCSS
  - Flowbite React

- バックエンド
  - Laravel
  - MySQL
  - OpenAI API
