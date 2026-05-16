<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookmakerBlogResource\Pages;
use App\Models\Bookmaker;
use App\Models\BookmakerBlog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BookmakerBlogResource extends Resource
{
    protected static ?string $model = BookmakerBlog::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Blogs Bookmakers';
    protected static ?string $modelLabel      = 'Blog Bookmaker';
    protected static ?string $pluralModelLabel = 'Blogs Bookmakers';
    protected static ?string $navigationGroup = 'Bookmakers';
    protected static ?int    $navigationSort  = 12;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Bookmaker & Code promo')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('bookmaker_id')
                        ->label('Bookmaker')
                        ->options(Bookmaker::active()->ordered()->pluck('name', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('promo_code')
                        ->label('Code promo')
                        ->default('COTA')
                        ->required()
                        ->maxLength(50),

                    Forms\Components\TextInput::make('bonus_title')
                        ->label('Titre du bonus')
                        ->placeholder('ex: Bonus 200% jusqu\'à 100 000 FCFA')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('bonus_description')
                        ->label('Description du bonus')
                        ->placeholder('Décrivez le bonus en détail...')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('cta_label')
                        ->label('Texte du bouton')
                        ->default("S'inscrire et obtenir le bonus")
                        ->maxLength(100)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Étapes du guide')
                ->description('Ajoutez les étapes numérotées pour guider l\'utilisateur')
                ->schema([
                    Forms\Components\Repeater::make('steps')
                        ->label('Étapes')
                        ->addActionLabel('Ajouter une étape')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Titre de l\'étape')
                                ->placeholder('ex: Créer votre compte')
                                ->required(),

                            Forms\Components\TextInput::make('icon')
                                ->label('Icône (emoji)')
                                ->placeholder('ex: 📝')
                                ->maxLength(10),

                            Forms\Components\Textarea::make('body')
                                ->label('Description')
                                ->placeholder('ex: Clique sur le bouton "S\'inscrire", remplis tes informations personnelles...')
                                ->rows(2)
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->defaultItems(0)
                        ->reorderable()
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bookmaker.name')
                    ->label('Bookmaker')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('promo_code')
                    ->label('Code promo')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('bonus_title')
                    ->label('Bonus')
                    ->limit(40)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('steps')
                    ->label('Étapes')
                    ->formatStateUsing(fn($state) => is_array($state) ? count($state) . ' étape(s)' : '0')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('bookmaker_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBookmakerBlogs::route('/'),
            'create' => Pages\CreateBookmakerBlog::route('/create'),
            'edit'   => Pages\EditBookmakerBlog::route('/{record}/edit'),
        ];
    }
}
