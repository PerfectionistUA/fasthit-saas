# Мультитенантні ролі та дозволи (Spatie Laravel Permission 6 + teams)

---

Цей документ описує, як реалізовано систему ролей та дозволів у FastHit SaaS з підтримкою мультитенантності, використовуючи пакет [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/introduction).

Ми використовуємо функціонал команд (Teams) Spatie для розмежування ролей та дозволів між різними тенантами.

## 1. Ключові принципи

1.  **Тенант як "Команда":** Кожен `Tenant` у нашій системі розглядається Spatie як окрема "команда" (`team`). Це дозволяє користувачеві мати різні ролі та дозволи в контексті кожного тенанта.
2.  **Глобальні ролі та дозволи:** Окрім тенант-специфічних ролей, існують також глобальні ролі та дозволи, які діють незалежно від тенанта.
3.  **Ідентифікатор тенанта (`tenant_id`):** Для розрізнення контекстів Spatie використовує поле `tenant_id` у своїх pivot-таблицях.

## 2. Структура Бази Даних (Primary Key)

Для забезпечення коректної роботи мультитенантності, первинні ключі на pivot-таблицях Spatie включають `tenant_id`. Це дозволяє уникнути конфліктів унікальності, коли один і той же користувач має однакову роль у різних тенантах.

> TL;DR – **tenant_id = 0 ≙ глобальний контекст.**  
> Усі запити Spatie потрібно робити **або** у `globalTeamId()` (0), **або** у конкретному `tenant_id` через `PermissionRegistrar::setPermissionsTeamId()`.

---

## 1. Структура таблиць ↔ PK

| Таблиця                | Первинний ключ (PK)                              | Додатково                |
|------------------------|--------------------------------------------------|--------------------------|
| `roles`                | `id` _(bigint)_                                  | **tenant_id NOT NULL**¹  |
| `permissions`          | `id` _(bigint)_                                  |                          |
| `model_has_roles`      | `(role_id, model_type, model_id, tenant_id)`     | **tenant_id NOT NULL**   |
| `model_has_permissions`| `(permission_id, model_type, model_id, tenant_id)`| **tenant_id NOT NULL**   |

¹ `tenant_id = 0` – _глобальна роль_ (видно всім тенантам). Будь-яка інша – роль/дозвіл виключно всередині свого тенанта.

**Важливо:** Це забезпечує, що комбінація "користувач + роль" є унікальною **не лише в межах певного тенанта або глобального контексту**.

---

## 2. Налаштування пакета

`config/permission.php`

```php
'teams'          => true,
'team_foreign_key' => 'tenant_id',
'global_team_id'   => 0,   // 👈 “нульовий” – глобальна команда
```

(app/Support/helpers.php)
```php
if (! function_exists('globalTeamId')) {
    function globalTeamId(): int
    {
        return (int) config('permission.global_team_id', 0);
    }
}```

`AppServiceProvider::boot()`

```php
app(PermissionRegistrar::class)->setPermissionsTeamId(globalTeamId());
```
> Це означає, що за замовчанням увесь код виконується у глобальному контексті.

## 3. Призначення та Відкликання Ролей/Дозволів

---

### 3.1. Глобальні ролі та дозволи

Призначаються без вказання `tenant_id`. Spatie автоматично зберігає їх з `tenant_id = 0`.

```php
$user->assignRole('super-admin');          // tenant_id = 0
$user->givePermissionTo('view-dashboard'); // tenant_id = 0
```

### 3.2. Тенант-специфічні ролі та дозволи

```php
u$registrar = app(PermissionRegistrar::class);
$registrar->setPermissionsTeamId($tenant->id);

$user->assignRole('editor');               // tenant_id = $tenant->id
$user->givePermissionTo('create_post');    // tenant_id = $tenant->id

$registrar->setPermissionsTeamId(null);    // повертаємося у global
```

> Не передавай другий аргумент assignRole($role, $teamId) – це deprecated.
> Канонічний спосіб – повністю покладатися на setPermissionsTeamId().*

---

## 4. Перевірка доступу

```php
// у глобальному коді:
$user->hasRole('super-admin');                     // true/false

// усередині тенанта:
app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);
$user->can('create_post');                         // true/false
```

---

## 2. Observer: автоматичне відкликання доступу

`app/Observers/UserObserver.php`

при status = suspended | inactive | soft-delete

- відкликає всі ролі/дозволи у кожному tenant-контексті

- видаляє Sanctum-токени

- чистить кеш Spatie\Permission

(див. реалізацію у коді репозиторію).

Обсервер UserObserver відповідає за автоматичне відкликання доступу користувача при зміні його статусу або при м'якому видаленні.

Коли статус користувача змінюється на suspended або inactive, обсервер викликає метод revokeUserAccess().

При м'якому видаленні користувача (soft-delete), також викликається revokeUserAccess() та від'єднання користувача від усіх тенантів.

Метод revokeUserAccess() використовує syncRoles([]) та syncPermissions([]) як для глобальних ролей (без tenant_id), так і для ролей у кожному тенанті (з явним tenant_id). Це гарантує повне очищення доступу.

---

## Seeder-и

*RoleSeeder*

- super-admin → tenant_id = 0

- шаблонні ролі (без tenant_id) – копіюються у кожен новий тенант за потреби.

*SuperAdminSeeder*

- створює користувача й призначає йому super-admin у глобальній команді.

---

## 7. Пам’ятка розробнику

1. Завжди явно встановлюй setPermissionsTeamId() перед масовими операціями Spatie.

2. Ніколи не зберігай значення tenant_id = NULL – унікальний PK це забороняє.

3. Для глобальних ролей використовується tenant_id = 0.

4. У тестах:

```php
$this->app[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);
і не забувай повертати null наприкінці.
```

При написанні тестів для ролей та дозволів важливо враховувати tenant_id. Використовуйте RefreshDatabase та імітуйте призначення ролей як глобально, так і для конкретних тенантів, а потім перевіряйте стан бази даних за допомогою assertDatabaseHas та assertDatabaseMissing, вказуючи tenant_id

---
