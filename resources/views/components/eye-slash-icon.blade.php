@props(['class' => '', 'width' => '17', 'height' => '17', 'color' => 'currentColor'])

<svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
    class="{{ $class }}">

    <!-- Eye (SAMA PERSIS dengan icon eye kamu) -->
    <path d="M12 4.5C7 4.5 2.73 7.61 1 12
           c1.73 4.39 6 7.5 11 7.5
           s9.27-3.11 11-7.5
           c-1.73-4.39-6-7.5-11-7.5
           z
           M12 17
           c-2.76 0-5-2.24-5-5
           s2.24-5 5-5
           s5 2.24 5 5
           s-2.24 5-5 5
           z
           M12 9
           c-1.66 0-3 1.34-3 3
           s1.34 3 3 3
           s3-1.34 3-3
           s-1.34-3-3-3z" fill="{{ $color }}" />

    <!-- Slash -->
    <path d="M4 3.5L20.5 20
           l-1.5 1.5
           L2.5 5z" fill="{{ $color }}" />
</svg>
