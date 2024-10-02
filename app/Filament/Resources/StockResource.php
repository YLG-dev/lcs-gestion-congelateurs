<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Produit;
use App\Models\Stock;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ReplicateAction;


class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('produit_id')
                    ->relationship('produit', 'nom')

                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->editOptionForm([
                        Forms\Components\TextInput::make('nom')
                            ->required(),
                    ]),
                Forms\Components\Select::make('congelateur')
                    ->options(['Grand' => 'Grand', 'Petit' => 'Petit', 'Menimur' => 'Menimur'])
                    ->default('Grand')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('poids')
                    ->numeric()
                    ->prefix('Poids en g')
                    ->suffix('g'),
                Forms\Components\Select::make('etage')
                    ->options([1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7])
                    ->default(1)
                    ->searchable(),
                Forms\Components\DatePicker::make('date_entree')
                    ->date()
                    ->default('now'),
                Forms\Components\DatePicker::make('date_sortie')
                    ->date()
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100, 'all'])
            ->defaultPaginationPageOption(100)
            ->columns([
                Tables\Columns\TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->sortable()
                    ->searchable(isIndividual: true),

                Tables\Columns\TextColumn::make('poids')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => $state . ' g'),
                Tables\Columns\TextColumn::make('congelateur')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('etage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_entree')
                    ->date()
                    ->label("Date d'entrée")
                    ->sortable()
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('date_sortie')
                    ->label('Date de sortie')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ReplicateAction::make()
                    ->requiresConfirmation(false)
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->filters([
                Filter::make('date_sortie')
                    ->label('Ne pas afficher les produits sortis')
                    ->query(fn(Builder $query): Builder => $query->whereNull('date_sortie'))
                    ->default(),
                Filter::make('date_entrée')
                    ->label("Produits entrés il y a plus d'un an ou sans date d'entree")
                    ->query(fn(Builder $query): Builder => $query
                        ->whereDate('date_entree', '<', Carbon::now()->subYear())
                        ->orWhereNull('date_entree')),
                SelectFilter::make('congelateur')
                    ->label('Congelateur')
                    ->options([
                        'Petit' => 'Petit',
                        'Grand' => 'Grand',
                        'Menimur' => 'Menimur',
                    ]),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(1);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}



