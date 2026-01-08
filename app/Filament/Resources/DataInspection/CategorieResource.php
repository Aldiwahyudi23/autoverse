<?php

namespace App\Filament\Resources\DataInspection;

use App\Filament\Resources\DataInspection\CategorieResource\Pages;
use App\Filament\Resources\DataInspection\CategoriesResource\RelationManagers\AppMenuRelationManager;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\MenuPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategorieResource extends Resource
{
    protected static ?string $model = Categorie::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Kategori Inspeksi';
    protected static ?string $pluralModelLabel = 'Kategori Inspeksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\Hidden::make('order')
                    ->required()
                    ->default(0),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->inline(false),

                Forms\Components\Select::make('settings.menu_model')
                    ->label('Model Menu')
                    ->options([
                        'horizontal' => 'Slid Kesamping',
                        'vertical'   => 'Tombol ko Menu',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('settings.position')
                    ->label('Posisi Menu')
                    ->options(function (callable $get) {
                        if ($get('settings.menu_model') === 'vertical') {
                            return [
                                'top-left'     => 'Atas Kiri',
                                'top-right'    => 'Atas Kanan',
                                'bottom-left'  => 'Bawah Kiri',
                                'bottom-right' => 'Bawah Kanan',
                            ];
                        }

                        return [
                            'top'    => 'Atas',
                            'bottom' => 'Bawah',
                        ];
                    })
                    ->required()
                    ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('No')
                    ->formatStateUsing(fn ($record) => $record->row_number),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('appMenu_count')
                    ->label('Jumlah Menu')
                    ->counts('appMenu')
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // Action untuk duplikasi single category
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikat')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->action(function (Categorie $record) {
                        $newCategory = self::duplicateCategoryWithRelations($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Kategori berhasil diduplikasi')
                            ->body('Kategori "' . $record->name . '" telah diduplikasi menjadi "' . $newCategory->name . '"')
                            ->success()
                            ->send();
                        
                        // Redirect ke kategori baru
                        return redirect(CategorieResource::getUrl('edit', ['record' => $newCategory]));
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Duplikasi Kategori')
                    ->modalSubheading(fn (Categorie $record) => 
                        'Apakah Anda yakin ingin menduplikasi kategori "' . $record->name . '"? ' .
                        'Kategori baru akan dibuat dengan:' . PHP_EOL .
                        '• ' . $record->appMenu()->count() . ' App Menu' . PHP_EOL .
                        '• ' . $record->appMenu()->withCount('menu_point')->get()->sum('menu_point_count') . ' Menu Point'
                    )
                    ->modalButton('Duplikat Sekarang'),
                    
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    
                    // Bulk action untuk duplikasi
                    Tables\Actions\BulkAction::make('duplicate')
                        ->label('Duplikat Kategori Terpilih')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $duplicatedCount = 0;
                            
                            foreach ($records as $originalCategory) {
                                self::duplicateCategoryWithRelations($originalCategory);
                                $duplicatedCount++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Kategori berhasil diduplikasi')
                                ->body($duplicatedCount . ' kategori telah diduplikasi dengan semua relasinya')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Duplikasi Kategori')
                        ->modalSubheading(fn (Collection $records) => 
                            'Apakah Anda yakin ingin menduplikasi ' . $records->count() . ' kategori?' . PHP_EOL .
                            'Kategori baru akan dibuat dengan semua App Menu dan Menu Point-nya.'
                        )
                        ->modalButton('Duplikat Sekarang')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    /**
     * Method untuk menduplikasi kategori dengan semua relasinya
     */
    protected static function duplicateCategoryWithRelations(Categorie $originalCategory): Categorie
    {
        // 1. Duplikat Kategori dengan cara manual untuk menghindari virtual attributes
        $newCategory = new Categorie();
        $newCategory->name = $originalCategory->name . ' (Copy)';
        $newCategory->settings = $originalCategory->settings; // Copy settings
        $newCategory->order = Categorie::max('order') + 1;
        $newCategory->is_active = $originalCategory->is_active;
        
        // Copy soft delete status jika ada
        if ($originalCategory->trashed()) {
            $newCategory->deleted_at = $originalCategory->deleted_at;
        }
        
        $newCategory->save();
        
        // 2. Duplikat AppMenu (relasi level 1)
        $originalCategory->load('appMenu.menu_point');
        
        $appMenuOrder = 1;
        foreach ($originalCategory->appMenu as $originalAppMenu) {
            // Duplikat AppMenu dengan cara manual
            $newAppMenu = new AppMenu();
            $newAppMenu->category_id = $newCategory->id;
            $newAppMenu->name = $originalAppMenu->name . ' (Copy)';
            $newAppMenu->input_type = $originalAppMenu->input_type;
            $newAppMenu->is_active = $originalAppMenu->is_active;
            $newAppMenu->order = $appMenuOrder++;
            
            // Copy soft delete status jika ada
            if ($originalAppMenu->trashed()) {
                $newAppMenu->deleted_at = $originalAppMenu->deleted_at;
            }
            
            $newAppMenu->save();
            
            // 3. Duplikat MenuPoint (relasi level 2)
            if ($originalAppMenu->menu_point && $originalAppMenu->menu_point->isNotEmpty()) {
                $menuPointOrder = 1;
                foreach ($originalAppMenu->menu_point as $originalMenuPoint) {
                    // Duplikat MenuPoint dengan cara manual
                    $newMenuPoint = new MenuPoint();
                    $newMenuPoint->app_menu_id = $newAppMenu->id;
                    $newMenuPoint->inspection_point_id = $originalMenuPoint->inspection_point_id;
                    $newMenuPoint->input_type = $originalMenuPoint->input_type;
                    $newMenuPoint->settings = $originalMenuPoint->settings; // Copy settings
                    $newMenuPoint->is_active = $originalMenuPoint->is_active;
                    $newMenuPoint->is_default = $originalMenuPoint->is_default;
                    $newMenuPoint->order = $menuPointOrder++;
                    
                    // Copy soft delete status jika ada
                    if ($originalMenuPoint->trashed()) {
                        $newMenuPoint->deleted_at = $originalMenuPoint->deleted_at;
                    }
                    
                    $newMenuPoint->save();
                }
            }
        }
        
        return $newCategory;
    }

    public static function getRelations(): array
    {
        return [
            AppMenuRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'view' => Pages\ViewCategorie::route('/{record}'),
            'edit' => Pages\EditCategorie::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
    
    /**
     * Menambahkan nomor urut untuk setiap baris
     */
    public static function getRowNumber($query, $record): int
    {
        return $query->where('id', '<=', $record->id)->count();
    }
}