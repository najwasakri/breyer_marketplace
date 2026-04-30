<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db_connect.php';

function ensureUserProfileColumns(PDO $pdo)
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $databaseName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!$databaseName) {
        return;
    }

    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $statement->execute([$databaseName, 'users', 'profile_image_path']);

    if ((int) $statement->fetchColumn() === 0) {
        $pdo->exec('ALTER TABLE users ADD COLUMN profile_image_path VARCHAR(255) NULL AFTER password_hash');
    }

    $checked = true;
}

function getProfileUploadDirectory()
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_photos';
}

function getProfilePublicPath($fileName)
{
    return '../uploads/profile_photos/' . $fileName;
}

function deleteProfileImageFile($publicPath)
{
    if ($publicPath === '') {
        return;
    }

    $uploadDirectory = getProfileUploadDirectory();
    $uploadDirectoryReal = realpath($uploadDirectory);
    if ($uploadDirectoryReal === false) {
        return;
    }

    $relativePath = ltrim(str_replace('../', '', $publicPath), '/\\');
    $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
    $resolvedPath = realpath($absolutePath);

    if ($resolvedPath !== false && strpos($resolvedPath, $uploadDirectoryReal) === 0 && is_file($resolvedPath)) {
        @unlink($resolvedPath);
    }
}

ensureUserProfileColumns($pdo);

$profileFlash = $_SESSION['student_profile_flash'] ?? null;
unset($_SESSION['student_profile_flash']);

$userStatement = $pdo->prepare(
    'SELECT user_id, full_name, ic_number, profile_image_path
     FROM users
     WHERE user_id = ?
     LIMIT 1'
);
$userStatement->execute([$_SESSION['user_id']]);
$currentUser = $userStatement->fetch();

if (!$currentUser) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$submittedName = null;
$submittedIc = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $submittedName = trim((string) ($_POST['full_name'] ?? ''));
    $submittedIc = preg_replace('/\D+/', '', (string) ($_POST['ic_number'] ?? ''));
    $removeProfileImage = isset($_POST['remove_profile_image']);
    $uploadedFile = $_FILES['profile_image'] ?? null;

    if ($submittedName === '') {
        $profileFlash = ['type' => 'error', 'message' => 'Nama pelajar wajib diisi.'];
    } elseif (mb_strlen($submittedName) > 100) {
        $profileFlash = ['type' => 'error', 'message' => 'Nama pelajar terlalu panjang. Maksimum 100 aksara.'];
    } elseif (strlen($submittedIc) !== 12) {
        $profileFlash = ['type' => 'error', 'message' => 'No. Kad Pengenalan mesti mengandungi 12 digit.'];
    } else {
        $icCheckStatement = $pdo->prepare('SELECT user_id FROM users WHERE ic_number = ? AND user_id <> ? LIMIT 1');
        $icCheckStatement->execute([$submittedIc, $_SESSION['user_id']]);

        if ($icCheckStatement->fetch()) {
            $profileFlash = ['type' => 'error', 'message' => 'No. Kad Pengenalan ini sudah digunakan oleh akaun lain.'];
        } else {
            $nextProfileImagePath = trim((string) ($currentUser['profile_image_path'] ?? ''));
            $newUploadPublicPath = null;

            if ($uploadedFile && ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (($uploadedFile['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    $profileFlash = ['type' => 'error', 'message' => 'Gambar profil gagal dimuat naik. Sila cuba lagi.'];
                } else {
                    $originalName = (string) ($uploadedFile['name'] ?? '');
                    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $temporaryFile = (string) ($uploadedFile['tmp_name'] ?? '');
                    $fileSize = (int) ($uploadedFile['size'] ?? 0);

                    if (!in_array($extension, $allowedExtensions, true)) {
                        $profileFlash = ['type' => 'error', 'message' => 'Hanya fail JPG, JPEG, PNG atau WEBP dibenarkan untuk gambar profil.'];
                    } elseif ($temporaryFile === '' || @getimagesize($temporaryFile) === false) {
                        $profileFlash = ['type' => 'error', 'message' => 'Fail gambar profil tidak sah atau rosak.'];
                    } elseif ($fileSize > 5 * 1024 * 1024) {
                        $profileFlash = ['type' => 'error', 'message' => 'Saiz gambar profil terlalu besar. Maksimum 5MB.'];
                    } else {
                        $uploadDirectory = getProfileUploadDirectory();

                        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
                            $profileFlash = ['type' => 'error', 'message' => 'Folder gambar profil tidak dapat disediakan buat masa ini.'];
                        } else {
                            $fileName = 'profile_' . (int) $_SESSION['user_id'] . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                            $targetFilePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
                            $newUploadPublicPath = getProfilePublicPath($fileName);

                            if (!move_uploaded_file($temporaryFile, $targetFilePath)) {
                                $profileFlash = ['type' => 'error', 'message' => 'Gambar profil tidak dapat disimpan. Sila cuba lagi.'];
                            } else {
                                $nextProfileImagePath = $newUploadPublicPath;
                            }
                        }
                    }
                }
            }

            if (!$profileFlash && $removeProfileImage) {
                if ($nextProfileImagePath !== '' && $newUploadPublicPath === null) {
                    deleteProfileImageFile($nextProfileImagePath);
                }
                $nextProfileImagePath = '';
            }

            if (!$profileFlash) {
                $updateStatement = $pdo->prepare('UPDATE users SET full_name = ?, ic_number = ?, profile_image_path = ? WHERE user_id = ?');
                $updateStatement->execute([
                    $submittedName,
                    $submittedIc,
                    $nextProfileImagePath !== '' ? $nextProfileImagePath : null,
                    $_SESSION['user_id'],
                ]);

                if ($newUploadPublicPath !== null && !empty($currentUser['profile_image_path'])) {
                    deleteProfileImageFile((string) $currentUser['profile_image_path']);
                }

                $_SESSION['name'] = $submittedName;
                $_SESSION['ic'] = $submittedIc;
                $_SESSION['student_profile_flash'] = ['type' => 'success', 'message' => 'Profil berjaya dikemaskini.'];

                header('Location: student_profile.php');
                exit;
            }

            if ($profileFlash && $newUploadPublicPath !== null && $nextProfileImagePath === $newUploadPublicPath) {
                deleteProfileImageFile($newUploadPublicPath);
            }
        }
    }
}

$userStatement->execute([$_SESSION['user_id']]);
$currentUser = $userStatement->fetch();

if (!$currentUser) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

$studentName = $submittedName !== null && $profileFlash && $submittedName !== ''
    ? $submittedName
    : trim((string) ($currentUser['full_name'] ?? $_SESSION['name'] ?? 'Pelajar Breyer'));

$studentIc = $submittedIc !== null && $profileFlash && $submittedIc !== ''
    ? $submittedIc
    : trim((string) ($currentUser['ic_number'] ?? $_SESSION['ic'] ?? '-'));

$_SESSION['name'] = $currentUser['full_name'] ?? $studentName;
$_SESSION['ic'] = $currentUser['ic_number'] ?? $studentIc;

$studentProfileImage = trim((string) ($currentUser['profile_image_path'] ?? ''));
$displayProfileImage = $studentProfileImage !== '' ? $studentProfileImage : 'ads0/breyer-logo-profile.png';
$icDigits = preg_replace('/\D+/', '', $studentIc);
$studentHandle = $icDigits !== ''
    ? 'student-' . substr(str_pad($icDigits, 4, '0', STR_PAD_LEFT), -4)
    : 'student-breyer';
$studentInitial = strtoupper(function_exists('mb_substr') ? mb_substr($studentName, 0, 1) : substr($studentName, 0, 1));
$hasCustomProfileImage = $studentProfileImage !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Pelajar - Breyer</title>
    <style>
        :root {
            --bg: #f3f7ff;
            --panel: rgba(255, 255, 255, 0.88);
            --line: rgba(31, 94, 255, 0.14);
            --text: #17304d;
            --muted: #61758b;
            --accent: #1f5eff;
            --accent-soft: rgba(31, 94, 255, 0.16);
            --accent-secondary: #d7263d;
            --accent-secondary-soft: rgba(215, 38, 61, 0.16);
            --success-soft: rgba(33, 181, 115, 0.14);
            --success-text: #136b45;
            --error-soft: rgba(215, 38, 61, 0.14);
            --error-text: #9b1e30;
            --shadow: 0 24px 48px rgba(31, 63, 122, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
        }

        .mobile-back {
            display: none;
        }

        .profile-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
            background:
                radial-gradient(circle at top right, rgba(31, 94, 255, 0.18), transparent 24%),
                radial-gradient(circle at bottom left, rgba(215, 38, 61, 0.12), transparent 22%),
                linear-gradient(180deg, #ffffff 0%, #edf4ff 52%, #f8fbff 100%);
        }

        .button-reset {
            appearance: none;
            border: none;
            background: none;
            font: inherit;
            color: inherit;
            padding: 0;
            cursor: pointer;
        }

        .settings-pane {
            background: var(--panel);
            border-right: 1px solid var(--line);
            box-shadow: 12px 0 32px rgba(31, 63, 122, 0.06);
            backdrop-filter: blur(18px);
            padding: 24px 26px 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .pane-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .desktop-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 16px;
            text-decoration: none;
            color: var(--text);
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(31, 94, 255, 0.12);
        }

        .desktop-back svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
        }

        .pane-title {
            font-size: 1.08rem;
            font-weight: 700;
            color: #183b62;
        }

        .search-box {
            position: relative;
        }

        .search-box svg {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            stroke: #6d84a1;
        }

        .search-box input {
            width: 100%;
            height: 60px;
            border-radius: 999px;
            border: 2px solid rgba(31, 94, 255, 0.88);
            background: rgba(255, 255, 255, 0.96);
            color: var(--text);
            padding: 0 18px 0 64px;
            font-size: 1rem;
            outline: none;
            box-shadow: 0 10px 20px rgba(31, 63, 122, 0.08);
        }

        .search-box input::placeholder {
            color: #7b8fa7;
        }

        .search-status {
            display: none;
            margin-top: -8px;
            font-size: 0.9rem;
            color: #5d7596;
        }

        .search-status.is-visible {
            display: block;
        }

        .suggest-card {
            display: flex;
            gap: 14px;
            align-items: flex-start;
            justify-content: space-between;
            padding: 18px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(238, 244, 255, 0.98) 100%);
            border: 1px solid var(--line);
            box-shadow: 0 14px 28px rgba(31, 63, 122, 0.08);
        }

        .suggest-icon {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 2px solid rgba(31, 94, 255, 0.16);
            background: linear-gradient(135deg, rgba(31, 94, 255, 0.10) 0%, rgba(215, 38, 61, 0.08) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .suggest-copy h2 {
            margin: 0 0 8px;
            font-size: 0.98rem;
            font-weight: 700;
        }

        .suggest-copy p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .suggest-copy strong {
            color: var(--accent-secondary);
        }

        .accent-button {
            min-width: 112px;
            padding: 12px 16px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent-secondary) 0%, #ff4d6d 100%);
            color: #ffffff;
            font-weight: 700;
            align-self: center;
        }

        .student-card {
            display: grid;
            gap: 14px;
            padding: 16px 0 8px;
        }

        .student-avatar {
            position: relative;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            box-shadow: 0 18px 36px rgba(31, 63, 122, 0.14);
            background: #dbe8ff;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-avatar-fallback {
            width: 100%;
            height: 100%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, var(--accent-secondary) 0%, var(--accent) 100%);
        }

        .student-avatar.fallback {
            background: transparent;
        }

        .student-avatar.fallback img {
            display: none;
        }

        .student-avatar.fallback .student-avatar-fallback {
            display: flex;
        }

        .avatar-chip {
            position: absolute;
            right: 10px;
            bottom: 10px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(31, 94, 255, 0.12);
            color: #214d95;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .student-name {
            margin: 0;
            font-size: 1.12rem;
            font-weight: 700;
            color: #16375a;
        }

        .student-handle {
            margin: 0;
            color: #6e8198;
            font-size: 0.95rem;
        }

        .student-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .meta-pill {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(31, 94, 255, 0.08);
            color: #214d95;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .settings-list {
            display: grid;
        }

        .settings-item {
            display: grid;
            grid-template-columns: 54px 1fr;
            gap: 14px;
            align-items: center;
            padding: 18px 6px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            width: 100%;
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .settings-item.active {
            background: rgba(31, 94, 255, 0.08);
            border-radius: 22px;
            border-bottom-color: transparent;
            padding-left: 14px;
            padding-right: 14px;
        }

        .settings-item-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6f8193;
        }

        .settings-item.active .settings-item-icon {
            background: rgba(31, 94, 255, 0.14);
            color: #ffffff;
        }

        .settings-item:hover {
            background: rgba(31, 94, 255, 0.05);
            border-radius: 22px;
            border-bottom-color: transparent;
            padding-left: 14px;
            padding-right: 14px;
        }

        .settings-item.is-hidden {
            display: none;
        }

        .settings-item-icon svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
        }

        .settings-item-title {
            margin: 0;
            font-size: 1.08rem;
            font-weight: 600;
            color: #17304d;
        }

        .settings-item-subtitle {
            margin: 4px 0 0;
            color: #667d93;
            line-height: 1.45;
            font-size: 0.95rem;
        }

        .detail-pane {
            padding: 34px 38px;
            min-width: 0;
            display: flex;
            align-items: flex-start;
            justify-content: center;
        }

        .profile-stage {
            width: min(100%, 920px);
            display: grid;
            gap: 28px;
            padding: 12px 0 36px;
        }

        .flash-banner {
            padding: 16px 20px;
            border-radius: 20px;
            font-weight: 600;
            line-height: 1.55;
            border: 1px solid transparent;
        }

        .flash-banner.success {
            background: var(--success-soft);
            color: var(--success-text);
            border-color: rgba(19, 107, 69, 0.14);
        }

        .flash-banner.error {
            background: var(--error-soft);
            color: var(--error-text);
            border-color: rgba(155, 30, 48, 0.14);
        }

        .hero-card {
            padding: 34px;
            border-radius: 34px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(240, 246, 255, 0.96) 100%);
            box-shadow: var(--shadow);
            border: 1px solid rgba(31, 94, 255, 0.08);
        }

        .hero-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 28px;
        }

        .hero-copy {
            max-width: 520px;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent-soft) 0%, var(--accent-secondary-soft) 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero-kicker::before {
            content: '';
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent-secondary);
        }

        .hero-copy h1 {
            margin: 0;
            font-size: clamp(2.4rem, 4vw, 3.5rem);
            line-height: 1.04;
            letter-spacing: -0.03em;
        }

        .hero-copy p {
            margin: 18px 0 0;
            color: var(--muted);
            font-size: 1.03rem;
            line-height: 1.65;
        }

        .hero-side {
            min-width: 220px;
            padding: 18px 20px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(31, 94, 255, 0.10);
        }

        .hero-side-label {
            display: block;
            color: #6b7f97;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .hero-side-value {
            display: block;
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .hero-side-note {
            color: var(--muted);
            line-height: 1.55;
            font-size: 0.92rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 26px;
        }

        .action-button,
        .form-button,
        .secondary-button,
        .ghost-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 18px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
        }

        .action-button,
        .secondary-button,
        .ghost-button {
            background: rgba(255, 255, 255, 0.94);
            color: var(--text);
            border: 1px solid rgba(31, 94, 255, 0.10);
        }

        .action-button.primary,
        .form-button {
            background: linear-gradient(135deg, var(--accent) 0%, #4d8dff 100%);
            color: #ffffff;
            border: none;
        }

        .ghost-button {
            background: linear-gradient(135deg, rgba(215, 38, 61, 0.08) 0%, rgba(255, 255, 255, 0.95) 100%);
            color: var(--accent-secondary);
            border-color: rgba(215, 38, 61, 0.18);
        }

        .tab-panel {
            display: none;
            gap: 28px;
        }

        .tab-panel.is-active {
            display: grid;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .info-card {
            padding: 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(31, 94, 255, 0.08);
        }

        .info-card-label {
            display: block;
            color: #8fa2ab;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .info-card-value {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .info-card-note {
            margin-top: 12px;
            color: var(--muted);
            line-height: 1.55;
            font-size: 0.92rem;
        }

        .detail-row-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.9fr;
            gap: 28px;
        }

        .single-column-card {
            display: grid;
        }

        .profile-form {
            display: grid;
            gap: 18px;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .field-group {
            display: grid;
            gap: 10px;
        }

        .field-group.full-width {
            grid-column: 1 / -1;
        }

        .field-group label {
            font-weight: 700;
            color: #16375a;
        }

        .text-input,
        .file-input {
            width: 100%;
            min-height: 54px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(31, 94, 255, 0.14);
            background: rgba(255, 255, 255, 0.96);
            color: var(--text);
            font: inherit;
            outline: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .text-input:focus,
        .file-input:focus {
            border-color: rgba(31, 94, 255, 0.45);
            box-shadow: 0 0 0 4px rgba(31, 94, 255, 0.10);
        }

        .helper-text {
            color: var(--muted);
            font-size: 0.9rem;
            line-height: 1.55;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .checkbox-row input {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-secondary);
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .detail-card,
        .mini-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.97) 0%, rgba(244, 248, 255, 0.98) 100%);
            border-radius: 28px;
            border: 1px solid rgba(31, 94, 255, 0.08);
            box-shadow: var(--shadow);
        }

        .detail-card {
            padding: 28px;
        }

        .detail-card h2,
        .mini-card h2 {
            margin: 0 0 18px;
            font-size: 1.45rem;
        }

        .detail-list {
            display: grid;
            gap: 18px;
        }

        .detail-list-row {
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .detail-list-row:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-list-label {
            display: block;
            color: #8fa2ab;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .detail-list-value {
            display: block;
            font-size: 1.08rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .mini-grid {
            display: grid;
            gap: 18px;
        }

        .mini-card {
            padding: 26px 24px;
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .mini-card-icon {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(31, 94, 255, 0.14) 0%, rgba(215, 38, 61, 0.12) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mini-card-icon svg {
            width: 34px;
            height: 34px;
            stroke: #214d95;
        }

        .mini-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(31, 94, 255, 0.16) 0%, rgba(215, 38, 61, 0.16) 100%);
            color: #17304d;
            font-weight: 700;
            width: fit-content;
        }

        .status-badge::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent-secondary);
        }

        .settings-empty {
            display: none;
            padding: 18px;
            border-radius: 20px;
            border: 1px dashed rgba(31, 94, 255, 0.20);
            color: var(--muted);
            line-height: 1.55;
        }

        .settings-empty.is-visible {
            display: block;
        }

        @media (max-width: 1200px) {
            .profile-shell {
                grid-template-columns: minmax(300px, 360px) minmax(0, 1fr);
            }

            .detail-pane {
                padding: 26px 24px;
            }
        }

        @media (max-width: 1024px) {
            .info-grid,
            .detail-row-grid,
            .field-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 940px) {
            .profile-shell {
                grid-template-columns: minmax(0, 1fr);
                grid-template-rows: auto auto;
            }

            .settings-pane {
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .detail-pane {
                align-items: flex-start;
                padding-top: 22px;
            }

        }

        @media (max-width: 720px) {
            .mobile-back {
                position: fixed;
                top: 14px;
                left: 14px;
                z-index: 20;
                width: 46px;
                height: 46px;
                border-radius: 14px;
                background: rgba(255, 255, 255, 0.94);
                border: 1px solid rgba(31, 94, 255, 0.12);
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                box-shadow: 0 12px 24px rgba(31, 63, 122, 0.10);
            }

            .mobile-back svg {
                width: 24px;
                height: 24px;
                stroke: #214d95;
            }

            .profile-shell {
                display: block;
            }

            .settings-pane,
            .detail-pane {
                padding-left: 18px;
                padding-right: 18px;
            }

            .settings-pane {
                padding-top: 78px;
            }

            .desktop-back {
                display: none;
            }

            .student-avatar {
                width: 140px;
                height: 140px;
            }

            .hero-card,
            .detail-card,
            .mini-card {
                border-radius: 24px;
            }

            .hero-card {
                padding: 24px;
            }

            .hero-top {
                flex-direction: column;
            }

            .hero-side {
                width: 100%;
                min-width: 0;
            }

            .suggest-card {
                flex-direction: column;
            }

            .accent-button {
                width: 100%;
            }
        }

        @media (max-width: 520px) {
            .settings-pane,
            .detail-pane {
                padding-left: 14px;
                padding-right: 14px;
            }

            .search-box input {
                height: 54px;
                padding-left: 58px;
            }

            .student-card {
                gap: 12px;
            }

            .student-avatar {
                width: 120px;
                height: 120px;
            }

            .hero-copy h1 {
                font-size: 2.1rem;
            }

            .detail-card,
            .mini-card,
            .hero-card {
                padding: 20px;
            }

            .hero-actions,
            .form-actions {
                grid-template-columns: 1fr;
                display: grid;
            }

            .settings-item {
                grid-template-columns: 48px 1fr;
                gap: 12px;
                padding: 16px 0;
            }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="mobile-back" aria-label="Kembali ke dashboard">
        <svg viewBox="0 0 24 24" fill="none">
            <path d="M15 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </a>

    <div class="profile-shell">
        <section class="settings-pane">
            <div class="pane-top">
                <a href="dashboard.php" class="desktop-back" aria-label="Kembali ke dashboard">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M15 18l-6-6 6-6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span>Kembali</span>
                </a>
                <div class="pane-title"><?php echo htmlspecialchars($studentName); ?></div>
            </div>

            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="6.5" stroke-width="2"></circle>
                    <path d="M16 16l4 4" stroke-width="2" stroke-linecap="round"></path>
                </svg>
                <input type="text" id="settingsSearch" placeholder="Search settings or profile sections">
            </div>
            <div class="search-status" id="searchStatus"></div>

            <div class="suggest-card">
                <div style="display:flex; gap:14px; align-items:flex-start; min-width:0;">
                    <div class="suggest-icon">
                        <svg viewBox="0 0 24 24" fill="none" width="24" height="24">
                            <path d="M12 3a6 6 0 0 0-3.9 10.56V17l1.8-1.1A6 6 0 1 0 12 3Z" stroke="#f2f5f6" stroke-width="2" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                    <div class="suggest-copy">
                        <h2>Edit profil anda</h2>
                        <p>Kemaskini nama, nombor IC dan gambar profil di panel utama. <strong>Buka sekarang</strong></p>
                    </div>
                </div>
                <button type="button" class="button-reset accent-button" data-tab-target="profile-panel">Edit profil</button>
            </div>

            <div class="student-card">
                <div class="student-avatar<?php echo $hasCustomProfileImage ? '' : ' fallback'; ?>" id="studentAvatar">
                    <img src="<?php echo htmlspecialchars($displayProfileImage); ?>" alt="Profile Pelajar" id="studentAvatarImage">
                    <div class="student-avatar-fallback"><?php echo htmlspecialchars($studentInitial); ?></div>
                    <span class="avatar-chip"><?php echo $hasCustomProfileImage ? 'Foto sendiri' : 'Default'; ?></span>
                </div>
                <div>
                    <p class="student-name"><?php echo htmlspecialchars($studentName); ?></p>
                    <p class="student-handle"><?php echo htmlspecialchars($studentHandle); ?></p>
                    <div class="student-meta">
                        <span class="meta-pill">IC: <?php echo htmlspecialchars($studentIc); ?></span>
                        <span class="meta-pill"><?php echo $hasCustomProfileImage ? 'Gambar disimpan' : 'Tiada gambar sendiri'; ?></span>
                    </div>
                </div>
            </div>

            <div class="settings-list" id="settingsList">
                <button type="button" class="button-reset settings-item active" data-tab-target="profile-panel" data-search="profile edit nama image gambar photo ic profile pelajar">
                    <div class="settings-item-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-width="2"></path>
                            <path d="M5 20a7 7 0 0 1 14 0" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-item-title">Profile</p>
                        <p class="settings-item-subtitle">Edit nama, gambar profil dan identiti pelajar</p>
                    </div>
                </button>

                <button type="button" class="button-reset settings-item" data-tab-target="account-panel" data-search="account akaun security session password detail">
                    <div class="settings-item-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 12h16" stroke-width="2" stroke-linecap="round"></path>
                            <path d="M7 7h10M7 17h10" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-item-title">Account</p>
                        <p class="settings-item-subtitle">Maklumat akaun dan tindakan keselamatan</p>
                    </div>
                </button>

                <button type="button" class="button-reset settings-item" data-tab-target="privacy-panel" data-search="privacy data privasi identity visibility protection">
                    <div class="settings-item-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect x="6" y="10" width="12" height="10" rx="2" stroke-width="2"></rect>
                            <path d="M9 10V8a3 3 0 0 1 6 0v2" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-item-title">Privacy</p>
                        <p class="settings-item-subtitle">Cara sistem melindungi data pelajar anda</p>
                    </div>
                </button>

                <button type="button" class="button-reset settings-item" data-tab-target="notifications-panel" data-search="notification alerts receipt purchase updates notification center">
                    <div class="settings-item-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 6a6 6 0 0 1 6 6v3.5l1.5 2.5H4.5L6 15.5V12a6 6 0 0 1 6-6Z" stroke-width="2" stroke-linejoin="round"></path>
                            <path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="settings-item-title">Notifications</p>
                        <p class="settings-item-subtitle">Notis ringkas tentang pembelian dan kemas kini akaun</p>
                    </div>
                </button>
            </div>
            <div class="settings-empty" id="settingsEmpty">Tiada padanan untuk carian itu. Cuba cari menggunakan kata kunci seperti profile, account, privacy atau notifications.</div>
        </section>

        <main class="detail-pane">
            <div class="profile-stage">
                <?php if ($profileFlash): ?>
                    <div class="flash-banner <?php echo $profileFlash['type'] === 'success' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($profileFlash['message'] ?? 'Terdapat kemas kini pada profil anda.'); ?>
                    </div>
                <?php endif; ?>

                <section class="hero-card">
                    <div class="hero-top">
                        <div class="hero-copy">
                            <div class="hero-kicker">Student Profile Center</div>
                            <h1><?php echo htmlspecialchars($studentName); ?></h1>
                            <p>Panel ini kini menyokong kemas kini profil sebenar. Anda boleh tukar nama, nombor kad pengenalan dan muat naik gambar profil terus dari halaman ini.</p>
                        </div>
                        <div class="hero-side">
                            <span class="hero-side-label">Student Handle</span>
                            <span class="hero-side-value"><?php echo htmlspecialchars($studentHandle); ?></span>
                            <div class="hero-side-note">Alias ini dikira daripada nombor kad pengenalan semasa dan akan berubah jika IC dikemaskini.</div>
                        </div>
                    </div>

                    
                </section>

                <section class="tab-panel is-active" id="profile-panel">
                    <section class="single-column-card">
                        <div class="detail-card">
                            <h2>Edit Maklumat Profil</h2>
                            <form method="post" enctype="multipart/form-data" class="profile-form">
                                <div class="field-grid">
                                    <div class="field-group">
                                        <label for="full_name">Nama Penuh</label>
                                        <input type="text" id="full_name" name="full_name" class="text-input" maxlength="100" value="<?php echo htmlspecialchars($studentName); ?>" required>
                                        <div class="helper-text">Nama ini akan terus menggantikan nama profil pelajar dalam sistem.</div>
                                    </div>

                                    <div class="field-group">
                                        <label for="ic_number">No. Kad Pengenalan</label>
                                        <input type="text" id="ic_number" name="ic_number" class="text-input" inputmode="numeric" pattern="[0-9]{12}" maxlength="12" value="<?php echo htmlspecialchars($studentIc); ?>" required>
                                        <div class="helper-text">Gunakan 12 digit tanpa simbol atau sengkang kerana nombor ini digunakan untuk log masuk.</div>
                                    </div>

                                    <div class="field-group full-width">
                                        <label for="profile_image">Gambar Profil</label>
                                        <input type="file" id="profile_image" name="profile_image" class="file-input" accept=".jpg,.jpeg,.png,.webp">
                                        <div class="helper-text">Format dibenarkan: JPG, JPEG, PNG, WEBP. Saiz maksimum 5MB.</div>
                                    </div>

                                    <div class="field-group full-width">
                                        <label class="checkbox-row" for="remove_profile_image">
                                            <input type="checkbox" id="remove_profile_image" name="remove_profile_image" value="1">
                                            <span>Buang gambar profil sedia ada dan guna semula avatar default</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="save_profile" value="1" class="button-reset form-button">Simpan Perubahan</button>
                                    <button type="button" class="button-reset secondary-button" data-tab-target="account-panel">Lihat panel akaun</button>
                                    <a href="change_password.php" class="ghost-button">Tukar Kata Laluan</a>
                                </div>
                            </form>
                        </div>
                    </section>
                </section>

                <section class="tab-panel" id="account-panel">
                    <div class="detail-row-grid">
                        <div class="detail-card">
                            <h2>Ringkasan Akaun</h2>
                            <div class="detail-list">
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Nama Akaun</span>
                                    <span class="detail-list-value"><?php echo htmlspecialchars($studentName); ?></span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Student Handle</span>
                                    <span class="detail-list-value"><?php echo htmlspecialchars($studentHandle); ?></span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Session Status</span>
                                    <span class="status-badge">Sedang log masuk</span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Gambar Profil</span>
                                    <span class="detail-list-value"><?php echo $hasCustomProfileImage ? 'Gambar tersimpan dalam akaun' : 'Masih menggunakan gambar default'; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="detail-card">
                            <h2>Tindakan Akaun</h2>
                            <div class="detail-list">
                                
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Kemaskini profil</span>
                                    <button type="button" class="button-reset action-button" data-tab-target="profile-panel">Buka panel profile</button>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Keselamatan</span>
                                    <a href="change_password.php" class="action-button">Tukar kata laluan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="tab-panel" id="privacy-panel">
                    <div class="detail-row-grid">
                        <div class="detail-card">
                            <h2>Privacy Center</h2>
                            <div class="detail-list">
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Protected Identity</span>
                                    <span class="detail-list-value">Maklumat IC hanya dipaparkan dalam halaman akaun pelajar ini dan digunakan untuk log masuk ke sistem.</span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Access Visibility</span>
                                    <span class="detail-list-value">Halaman ini memerlukan session pelajar aktif sebelum boleh dibuka atau dikemaskini.</span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Profile Image Storage</span>
                                    <span class="detail-list-value">Gambar profil disimpan dalam folder upload sistem dan dipautkan terus ke akaun anda.</span>
                                </div>
                            </div>
                        </div>
                        <div class="detail-card">
                            <h2>Privacy Actions</h2>
                            <div class="detail-list">
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Review profile info</span>
                                    <button type="button" class="button-reset action-button" data-tab-target="profile-panel">Semak maklumat profil</button>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Back to account</span>
                                    <button type="button" class="button-reset action-button" data-tab-target="account-panel">Semak status akaun</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="tab-panel" id="notifications-panel">
                    <div class="detail-row-grid">
                        <div class="detail-card">
                            <h2>Notification Preferences</h2>
                            <div class="detail-list">
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Purchase updates</span>
                                    <span class="detail-list-value">Dapatkan makluman untuk status pembelian dan pembayaran.</span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Receipt reminders</span>
                                    <span class="detail-list-value">Semak resit dan bukti bayaran dengan lebih cepat dari dashboard.</span>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Profile alerts</span>
                                    <span class="detail-list-value">Gunakan carian atau menu tab untuk terus ke panel berkaitan profil dan akaun.</span>
                                </div>
                            </div>
                        </div>
                        <div class="detail-card">
                            <h2>Notification Actions</h2>
                            <div class="detail-list">
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Profile</span>
                                    <button type="button" class="button-reset action-button" data-tab-target="profile-panel">Kembali ke profil</button>
                                </div>
                                <div class="detail-list-row">
                                    <span class="detail-list-label">Privacy</span>
                                    <button type="button" class="button-reset action-button" data-tab-target="privacy-panel">Buka privacy center</button>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        (function () {
            const avatar = document.getElementById('studentAvatar');
            const avatarImage = document.getElementById('studentAvatarImage');
            const searchInput = document.getElementById('settingsSearch');
            const searchStatus = document.getElementById('searchStatus');
            const emptyState = document.getElementById('settingsEmpty');
            const navButtons = Array.from(document.querySelectorAll('.settings-item'));
            const panels = Array.from(document.querySelectorAll('.tab-panel'));
            const tabTriggers = Array.from(document.querySelectorAll('[data-tab-target]'));
            const profileImageInput = document.getElementById('profile_image');
            const removeProfileCheckbox = document.getElementById('remove_profile_image');
            const defaultAvatarPath = 'ads0/breyer-logo-profile.png';

            function activatePanel(panelId) {
                navButtons.forEach(function (button) {
                    button.classList.toggle('active', button.dataset.tabTarget === panelId);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.id === panelId);
                });
            }

            function updateSearchResults(term) {
                const normalized = term.trim().toLowerCase();
                let firstMatch = null;
                let visibleCount = 0;

                navButtons.forEach(function (button) {
                    const haystack = (button.dataset.search + ' ' + button.textContent).toLowerCase();
                    const isMatch = normalized === '' || haystack.indexOf(normalized) !== -1;
                    button.classList.toggle('is-hidden', !isMatch);

                    if (isMatch) {
                        visibleCount += 1;
                        if (!firstMatch) {
                            firstMatch = button;
                        }
                    }
                });

                if (normalized === '') {
                    searchStatus.textContent = '';
                    searchStatus.classList.remove('is-visible');
                    emptyState.classList.remove('is-visible');
                    return;
                }

                if (firstMatch) {
                    activatePanel(firstMatch.dataset.tabTarget);
                    searchStatus.textContent = visibleCount + ' section found for "' + term + '"';
                    searchStatus.classList.add('is-visible');
                    emptyState.classList.remove('is-visible');
                } else {
                    searchStatus.textContent = 'No matching section for "' + term + '"';
                    searchStatus.classList.add('is-visible');
                    emptyState.classList.add('is-visible');
                }
            }

            function applyAvatarFallback() {
                if (!avatar) return;
                avatar.classList.add('fallback');
            }

            function setAvatarSource(source) {
                if (!avatar || !avatarImage) return;
                avatar.classList.remove('fallback');
                avatarImage.src = source;
            }

            // Use event delegation so clicks work even if elements are updated dynamically
            document.addEventListener('click', function (evt) {
                var trigger = evt.target.closest && evt.target.closest('[data-tab-target]');
                if (!trigger) return;
                var panelId = trigger.dataset && trigger.dataset.tabTarget;
                if (panelId) {
                    activatePanel(panelId);
                }
            });

            searchInput.addEventListener('input', function () {
                updateSearchResults(searchInput.value);
            });

            if (profileImageInput) {
                profileImageInput.addEventListener('change', function () {
                    const file = profileImageInput.files && profileImageInput.files[0] ? profileImageInput.files[0] : null;
                    if (!file) {
                        if (removeProfileCheckbox && removeProfileCheckbox.checked) {
                            setAvatarSource(defaultAvatarPath);
                        }
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        if (event.target && typeof event.target.result === 'string') {
                            setAvatarSource(event.target.result);
                            if (removeProfileCheckbox) {
                                removeProfileCheckbox.checked = false;
                            }
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            if (removeProfileCheckbox) {
                removeProfileCheckbox.addEventListener('change', function () {
                    if (removeProfileCheckbox.checked) {
                        setAvatarSource(defaultAvatarPath);
                    }
                });
            }

            if (avatarImage) {
                avatarImage.addEventListener('error', function () {
                    applyAvatarFallback();
                });
            }
        }());
    </script>
</body>
</html>
