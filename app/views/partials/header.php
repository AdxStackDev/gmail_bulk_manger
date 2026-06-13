<?php
/**
 * Shared page header / navigation bar partial.
 *
 * Expects the following variables to be defined by the including page:
 * @var string $headingText      Heading label
 * @var string $headingClasses   Tailwind gradient classes for the heading
 * @var array  $navLinks         List of ['href' => , 'label' => , 'class' => ]
 * @var string $authBtnClass     Tailwind classes for the Authorize button
 * @var string $signoutBtnClass  Tailwind classes for the Sign Out button
 */
?>
<div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
    <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r <?php echo $headingClasses; ?> text-center md:text-left">
        <?php echo $headingText; ?>
    </h1>
    <div class="flex flex-wrap justify-center items-center gap-4">
        <?php foreach (($navLinks ?? []) as $link): ?>
            <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $link['class']; ?> hover:underline font-medium text-sm md:text-base"><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></a>
        <?php endforeach; ?>
        <?php include __DIR__ . '/theme-toggle.php'; ?>
        <button id="authorize_button" onclick="handleAuthClick()" class="<?php echo $authBtnClass; ?> hidden text-sm md:text-base">Authorize</button>
        <button id="signout_button" onclick="handleSignoutClick()" class="<?php echo $signoutBtnClass; ?> hidden text-sm md:text-base">Sign Out</button>
    </div>
</div>
