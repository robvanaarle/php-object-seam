<?php

namespace PHPObjectSeam\TestClasses;

// phpcs:disable
class TestCUTPropertyHooks
{
    public int $publicX {
        get => $this->publicX * 2;
        set(int $x) => $this->publicX = $x * 3;
    }

    protected int $protectedX {
        get => $this->protectedX + 2;
        set(int $x) => $this->protectedX = $x + 3;
    }

    private int $privateX {
        get => $this->privateX - 2;
        set(int $x) => $this->privateX = $x - 3;
    }
}
