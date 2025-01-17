<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PreviewRenderer
{
    protected static bool $assetsIncluded = false;

    public function renderPreview(array $row): ?array
    {
        $preview = null;
        $fieldName = null;
        $headerContent = null;
        $drawItem = true;
        $itemContent = '<a name="c' . $row['uid'] . '"></a>';
        $providers = $this->getConfigurationService()->resolveConfigurationProviders('tt_content', $fieldName, $row);
        foreach ($providers as $provider) {
            /** @var ProviderInterface $provider */
            [$previewHeader, $previewContent, $continueDrawing] = $provider->getPreview($row);
            if (!empty($previewHeader)) {
                $headerContent = $previewHeader . (!empty($headerContent) ? ': ' . $headerContent : '');
                $drawItem = false;
            }
            if (!empty($previewContent)) {
                $itemContent .= $previewContent;
                $drawItem = false;
            }
            $preview = [$headerContent, $itemContent, $drawItem];
            if (false === $continueDrawing) {
                break;
            }
        }
        $this->attachAssets();
        return $preview;
    }

    protected function attachAssets(): void
    {
        if (!static::$assetsIncluded) {
            /** @var PageRenderer $pageRenderer */
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addCssFile('EXT:flux/Resources/Public/css/flux.css');
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Flux/FluxCollapse');

            static::$assetsIncluded = true;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getConfigurationService(): FluxService
    {
        /** @var FluxService $fluxService */
        $fluxService = GeneralUtility::makeInstance(FluxService::class);
        return $fluxService;
    }
}
