<?php

if (! function_exists('globalTeamId')) {
    function globalTeamId(): int
    {
        return (int) config('permission.global_team_id', 0);
    }
}
