<?php

namespace Yannelli\LaravelPlaud\Models\Responses;

use Yannelli\LaravelPlaud\Models\Workspace;

class ResponseWorkspaces
{
    /**
     * @param array<Workspace> $workspaces
     */
    public function __construct(
        public int $status,
        public string $msg = '',
        public array $workspaces = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $list = $data['data_workspace_list'] ?? $data['workspaces'] ?? $data['data'] ?? [];

        if (! is_array($list)) {
            $list = [];
        }

        foreach (['list', 'workspaces', 'items'] as $nested) {
            if (isset($list[$nested]) && is_array($list[$nested])) {
                $list = $list[$nested];
                break;
            }
        }

        $workspaces = [];
        foreach ($list as $item) {
            if (is_array($item)) {
                $workspaces[] = Workspace::fromArray($item);
            }
        }

        return new self(
            status: (int) ($data['status'] ?? 0),
            msg: (string) ($data['msg'] ?? ''),
            workspaces: $workspaces,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'msg' => $this->msg,
            'data_workspace_list' => array_map(fn (Workspace $workspace) => $workspace->toArray(), $this->workspaces),
        ];
    }
}
