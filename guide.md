### Changing the Singular and Plural Model Names

```php
protected static ?string $modelLabel = 'عميل';
protected static ?string $pluralModelLabel = 'عملاء';
```

This property is used to specify the singular name of the model. It can be used in any resource to change the default singular name of the model.

This property is used to specify the plural name of the model. It can be used in any resource to change the default plural name of the model.


### Generating the Model, Migration, and Factory at the Same Time

If you'd like to save time when scaffolding your resources, Filament can also generate the model, migration, and factory for the new resource at the same time using the `--model`, `--migration`, and `--factory` flags in any combination:

```bash
php artisan make:filament-resource Customer --model --migration --factory
```
