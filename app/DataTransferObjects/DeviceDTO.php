<?php

namespace App\DataTransferObjects;

class DeviceDTO extends DataTransferObjectBase
{
    public static function fromModel($deviceDetector): self
    {
        return new self($deviceDetector);
    }

    public function GetDTO(): array
    {
        return [
            'mobile' => $this->entity->isSmartphone() || $this->entity->isFeaturePhone() || $this->entity->isMobileApp(),
            'tablet' => $this->entity->isTablet() || $this->entity->isPhablet(),
            'pc' => $this->entity->isDesktop(),
            'tv' => $this->entity->isTV() || $this->entity->isSmartDisplay(),
            'camera' => $this->entity->isCamera(),
            'bot' => $this->entity->isBot(),
        ];
    }
}
