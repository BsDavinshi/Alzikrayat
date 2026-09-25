<?php
declare(strict_types=1);

final class View
{
    public static function render(string $viewName, array $viewData = [], ?string $layoutName = 'layout/main'): string
    {
        $content = self::partial($viewName, $viewData);

        if ($layoutName === null) {
            return $content;
        }

        return self::partial($layoutName, array_merge($viewData, ['content' => $content]));
    }

    public static function partial(string $viewName, array $viewData = []): string
    {
        $templateFile = BASE_PATH . '/views/' . $viewName . '.php';
        if (!is_file($templateFile)) {
            throw new RuntimeException("View template not found: {$viewName}");
        }

        extract($viewData, EXTR_SKIP);
        ob_start();
        try {
            require $templateFile;
        } catch (Throwable $renderError) {
            ob_end_clean();
            throw $renderError;
        }
        return (string) ob_get_clean();
    }
}
