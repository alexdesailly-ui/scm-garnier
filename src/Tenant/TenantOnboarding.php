<?php

declare(strict_types=1);

namespace SCM\Tenant;

use SCM\Core\App;
use SCM\Core\Database;

final class TenantOnboarding
{
    private Database $db;
    private TenantRepository $tenants;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->tenants = new TenantRepository($db);
    }

    public function provision(array $data): array
    {
        $slug = $this->sanitizeSlug($data['slug'] ?? '');
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $domain = trim($data['domain'] ?? '');
        $plan = $data['plan'] ?? 'starter';

        $errors = $this->validate($slug, $name, $email, $password);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $pdo = $this->db->pdo();
        $pdo->beginTransaction();

        try {
            $tenant = $this->tenants->create($slug, $name, $domain, $plan);

            $app = App::instance();
            $app->setTenant($tenant);

            $userModel = $app->users();
            $userId = $userModel->create([
                'email' => $email,
                'password' => $password,
                'full_name' => 'Administrateur',
                'role' => 'admin',
            ]);

            $settingModel = $app->settings();
            $settingModel->set('site_name', $name);
            $settingModel->set('site_description', "Cabinet infirmier — {$name}");
            $settingModel->set('email', $email);
            $settingModel->set('slot_duration', '30');
            $settingModel->set('max_advance_days', '30');
            $settingModel->set('opening_hours', 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00');

            $this->seedDefaultSlots($tenant->id);

            $pdo->commit();

            return [
                'success' => true,
                'tenant' => $tenant,
                'user_id' => $userId,
            ];
        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }

    private function validate(string $slug, string $name, string $email, string $password): array
    {
        $errors = [];

        if ($slug === '' || strlen($slug) < 3) {
            $errors[] = 'L\'identifiant doit contenir au moins 3 caractères.';
        }
        if (!preg_match('/^[a-z0-9][a-z0-9-]*[a-z0-9]$/', $slug)) {
            $errors[] = 'L\'identifiant ne peut contenir que des lettres minuscules, chiffres et tirets.';
        }
        if ($this->tenants->findBySlug($slug) !== null) {
            $errors[] = 'Cet identifiant est déjà pris.';
        }
        if ($name === '') {
            $errors[] = 'Le nom du cabinet est requis.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        return $errors;
    }

    private function sanitizeSlug(string $input): string
    {
        $slug = strtolower(trim($input));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function seedDefaultSlots(int $tenantId): void
    {
        $stmt = $this->db->pdo()->prepare(
            "INSERT INTO available_slots (tenant_id, nurse_id, day_of_week, start_time, end_time)
             VALUES (?, NULL, ?, ?, ?)"
        );

        for ($d = 1; $d <= 5; $d++) {
            $stmt->execute([$tenantId, $d, '07:00', '19:00']);
        }
        $stmt->execute([$tenantId, 6, '08:00', '12:00']);
    }
}
