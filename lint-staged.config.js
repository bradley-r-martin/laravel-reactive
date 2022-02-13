module.exports = {
    '*.{ts,tsx}': [() => 'tsc --skipLibCheck --noEmit', 'eslint --cache --fix'],
    '**/*': ['prettier --write --ignore-unknown'],
};
