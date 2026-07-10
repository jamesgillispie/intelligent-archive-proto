export const clampNumber = (num, a, b) =>
    Math.max(Math.min(num, Math.max(a, b)), Math.min(a, b))

export const getRandInt = (min, max) =>
    Math.floor(Math.random() * (max - min + 1)) + min
