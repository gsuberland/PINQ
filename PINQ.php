<?php

/*

PINQ is a collection of array/iterable manipulation functions for PHP, inspired by LINQ in .NET

I wrote this to lessen the cognitive burden of working with PHP's inbuilt array manipulation functions, and to minimise the number of times I needed to re-write implementations of this functionality inline in my apps.

Things PINQ is:
 - A collection of useful array/iterable manipulation functions for PHP.
 - Generally more descriptive than PHP's inbuilt array manipulation functions.
 - Pretty well documented. The phpdoc comments allow for strong type inference when using a suitable IDE (e.g. vscode + intelephense)
 - Something I wrote over the course of two evenings.
 - Probably a good starting point if you want to put some work in writing unit tests to find the bugs.

Things PINQ is NOT:
 - Production ready. PINQ SHOULD NOT BE USED IN PRODUCTION. DO NOT USE THIS IN PRODUCTION.
 - Well tested. This is a low-effort, poorly tested implementation.
 - Guaranteed to be correct. See above.
 - Fast. Approximately zero consideration was put into performance.
 - A port of the LINQ API surface or method behaviour. PINQ is inspired by LINQ, it is not a direct port. Some things are done differently.
 - Production ready. I'm serious. Don't use this for anything that matters. HAVE I SAID THIS ENOUGH YET?
 - Actively maintained. I have ADHD, I don't do "actively maintained". I work on things for a couple of days and get bored. Fork 'em if you got 'em.

Theoretically Asked Questions:
 - "Can I use this in production?" only if you like production being on fire, and also getting fired.
 - "Why use $params instead of `use` for closure variable capture?" I genuinely didn't know PHP had that feature until 80% of the way through writing this, but this does mean you can also just directly pass native functions too.
 - "It's been 6 months and you haven't answered my issue" that's not a question but I direct you to my aforementioned ADHD and PINQ not being actively maintained.
 - "Why did you do [x] this way?" because I wrote this in 2 evenings and the code was pretty much a stream of consciousness.
 - "Can you implement [some function]?" I am probably theoretically capable.
 - "Ok, *will* you implement [some function]?" There is very little chance that this will happen in practice.
 - ":(" look, my brain is basically a puppy that chugged six cans of red bull in a squirrel factory, and just I want to make it suuuuuper clear that the chances of me responding to literally anything about this approaches zero.

----

PINQ is released under MIT license.

Copyright 2024 Graham Sutherland

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

class PINQ
{
    /**
     * Determines whether all elements of a sequence satisfy a condition.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<mixed,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return bool True if every element of the source sequence passes the test in the specified predicate, or if the sequence is empty; otherwise false.
     */
    public static function All(iterable $source, callable $predicate, mixed ...$params) : bool
    {
        $index = 0;
        foreach ($source as $key => $element)
        {
            if (!$predicate($index, $key, $element, ...$params))
            {
                return false;
            }
            $index++;
        }
        return true;
    }

    /**
     * Determines whether any element of a sequence exists or satisfies a condition.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return bool True if the source sequence is not empty and at least one of its elements passes the test in the specified predicate; otherwise false.
     */
    public static function Any(iterable $source, callable $predicate, mixed ...$params) : bool
    {
        $index = 0;
        foreach ($source as $key => $element)
        {
            if ($predicate($index, $key, $element, ...$params))
            {
                return true;
            }
            $index++;
        }
        return false;
    }

    /**
     * Appends a value to the end of a sequence.
     * 
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<mixed,TSource> $source The source sequence.
     * @param TSource $value The value to append.
     * 
     * @return \Generator<mixed,TSource>
     */
    public static function Append(iterable $source, mixed $value) : \Generator
    {
        foreach ($source as $key => $elem)
        {
            yield $elem;
        }
        yield $value;
    }

    /**
     * Computes the mean average of values projected from the input sequence according to a value selector function.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param ?callable(int $index, TKey $key, TSource $elem, mixed ...$params):(int|float) $valueSelector A transform function to apply to each element, or null if the elements should be used directly.
     * @param mixed ...$params Additional parameters to be passed to the transform function.
     * 
     * @return ?float The mean average of the projected values, or null if the sequence contains no elements.
     */
    public static function Average(iterable $source, ?callable $valueSelector, mixed ...$params) : ?float
    {
        $directMap = fn(int $index, mixed $key, float|int $value, mixed ...$params) : float|int => $value;
        $accumulator = 0;
        $index = 0;
        foreach ($source as $key => $value)
        {
            if ($valueSelector === null)
            {
                $accumulator += $directMap($index, $key, $value, ...$params); 
            }
            else
            {
                $accumulator += $valueSelector($index, $key, $value, ...$params);
            }
            $index++;
        }
        if ($index === 0)
        {
            return null;
        }
        return $accumulator / floatval($index);
    }

    /**
     * Categorises the elements of a sequence according to a specified label selector function, allowing each element to be duplicated into any number of categories, with the elements of each category optionally projected by a specified value selector function.
     * 
     * This function allows you to categorise elements based on a label selector function, and optionally also project the values in each category using a value selector function.
     * The label selector function can return any number of labels, and elements will be placed into all categories matching those labels.
     * The category label is passed to the value selector function so that different projections may be accomplished depending on the category label.
     * This is distinct from GroupBy because it allows elements to be duplicated across categories, or placed into no category, rather than each source element going into exactly one category.
     * 
     * @template TSourceKey The type of the keys in the source sequence.
     * @template TSourceValue The type of the elements in the source sequence.
     * @template TLabelParam The type of the additional parameter passed to the label selector function.
     * @template TCategoryLabel The type of the category labels.
     * @template TCategoryValue The type of the elements in the lables.
     * @template TValueParam The type of the additional parameter passed to the value selector function.
     * 
     * @param iterable<TSourceKey,TSourceValue> $source The source sequence.
     * 
     * @param callable(int $index, TSourceKey $key, TSourceValue $elem, TLabelParam $param): TCategoryLabel[] $labelsSelector A function to extract the labels for each element.
     * The element is placed into each of the categories whose labels are returned.
     * The first parameter is the numeric index of the element in the source sequence.
     * The second parameter is the element's key in the source sequence.
     * The third parameter is the element value.
     * The fourth parameter is the value of `$keySelectorParam`.
     * 
     * @param TLabelParam $labelsSelectorParam An additional value to be passed to the label selector function.
     * 
     * @param ?callable(TCategoryLabel $categoryLabel, int $index, TSourceKey $key, TSourceValue $elem, TLabelParam $param): TCategoryValue $valueSelector A function to extract the value for each element.
     * The first parameter is the category label for this element selected by `$labelsSelector`.
     * The second parameter is the numeric index of the element in the source sequence.
     * The third parameter is the element's key in the source sequence.
     * The fourth parameter is the element value.
     * The fifth parameter is the value of `$valueSelectorParam`.
     * 
     * @param TValueParam $valueSelectorParam An additional value to be passed to the value selector function.
     * 
     * @return iterable<TCategoryLabel,TCategoryValue[]> A sequence of categories containing the categorised elements.
     */
    public static function CategoriseBy(iterable $source, callable $labelsSelector, mixed $labelsSelectorParam = null, ?callable $valueSelector = null, mixed $valueSelectorParam = null) : array
    {
        $index = 0;
        $categories = [];
        if ($valueSelector === null)
        {
            // default selector maps the element to itself
            $valueSelector = fn($g,$i,$k,$e,$p) => $e;
        }
        foreach ($source as $key => $item)
        {
            $categoryLabels = $labelsSelector($index, $key, $item, $labelsSelectorParam);
            foreach ($categoryLabels as $categoryLabel)
            {
                $value = $valueSelector($categoryLabel, $index, $key, $item, $valueSelectorParam);
                if (!array_key_exists($categoryLabel, $categories))
                {
                    $categories[$categoryLabel] = [];
                }
                array_push($categories, $value);
            }
            $index++;
        }
        return $categories;
    }

    /**
     * Splits the elements of a sequence into chunks of size at most `$size`.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $size The maximum size of each chunk.
     * @param bool $assoc If true, the returned chunks are associative and use the same keys as the source sequence. Otherwise, elements are sequentially pushed into the chunks in the order they are found in the source sequence.
     * 
     * @return TSource[][] Array chunks of size at most `$size`.
     */
    public static function Chunk(iterable $source, int $size, bool $assoc = false) : array
    {
        return iterator_to_array(self::ChunkLazy($source, $size, $assoc));
    }

    /**
     * Splits the elements of a sequence into chunks of size at most `$size`, using a generator for lazy execution.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $size The maximum size of each chunk.
     * @param bool $assoc If true, the returned chunks are associative and use the same keys as the source sequence. Otherwise, elements are sequentially pushed into the chunks in the order they are found in the source sequence.
     * 
     * @return \Generator<TKey,TSource[]> A generator that produces chunks of size at most `$size`.
     */
    public static function ChunkLazy(iterable $source, int $size, bool $assoc = false) : \Generator
    {
        if ($size < 1)
        {
            throw new \InvalidArgumentException('Chunk size must be greater than or equal to 1.');
        }

        $count = 0;
        $chunk = [];
        foreach ($source as $key => $element)
        {
            if ($assoc)
            {
                $chunk[$key] = $element;
            }
            else
            {
                array_push($chunk, $element);
            }
            $count++;
            if ($count == $size)
            {
                yield $chunk;
                $chunk = [];
                $count = 0;
            }
        }
        if (count($chunk) > 0)
        {
            yield $chunk;
        }
    }

    /**
     * Concatenates sequences together into a single sequence.
     * 
     * Keys in the source sequences are ignored.
     * 
     * @template TKey The type of the keys in the source sequences.
     * @template TSource The type of the elements in the source sequences.
     * 
     * @param iterable<TKey,TSource> ...$sources The source sequences.
     * 
     * @return \Generator<int,TSource> The concatenated sequence.
     */
    public static function Concat(iterable ...$sources) : \Generator
    {
        $i = 0;
        foreach ($sources as $source)
        {
            foreach ($source as $elem)
            {
                yield $i => $elem;
                $i++;
            }
        }
    }

    /**
     * Determines whether the count of elements of a sequence satisfying a condition meets or exceeds a specified threshold.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $minCount The minimum count of items satisfying the condition required.
     * @param callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return True if the source sequence contains at least `$minCount` elements that pass the test in the specified predicate; otherwise false.
     */
    public static function ContainsAtLeast(iterable $source, int $minCount, callable $predicate, mixed ...$params) : bool
    {
        $count = 0;
        $index = 0;
        foreach ($source as $key => $element)
        {
            if ($predicate($index, $key, $element, ...$params))
            {
                $count++;
                if ($count >= $minCount)
                {
                    return true;
                }
            }
            $index++;
        }
        return false;
    }

    /**
     * Determines whether the count of elements of a sequence satisfying a condition does not exceed a specified threshold.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $maxCount The maximum count of items satisfying the condition required.
     * @param callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return True if the source sequence contains at least `$minCount` elements that pass the test in the specified predicate; otherwise false.
     */
    public static function ContainsNoMoreThan(iterable $source, int $maxCount, callable $predicate, mixed ...$params) : bool
    {
        $count = 0;
        $index = 0;
        foreach ($source as $key => $element)
        {
            if ($predicate($index, $key, $element, ...$params))
            {
                $count++;
                if ($count > $maxCount)
                {
                    return false;
                }
            }
            $index++;
        }
        return true;
    }

    /**
     * Counts the elements of a sequence which satisfy a condition.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return The count of elements which pass the test in the specified predicate.
     */
    public static function Count(iterable $source, callable $predicate, mixed ...$params) : int
    {
        $count = 0;
        $index = 0;
        foreach ($source as $key => $element)
        {
            if ($predicate($index, $key, $element, ...$params))
            {
                $count++;
            }
            $index++;
        }
        return $count;
    }

    /**
     * Returns the first element of the sequence that satisfies a condition, or a specified default value if no such element is found.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param ?callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition, or null if all elements are to be accepted.
     * @param TSource $defaultValue The default value to return if the sequence is empty or if no element passes the test specified by `$predicate`.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return TSource `$defaultValue` if `$source` is empty or if no element passes the test specified by `$predicate`; otherwise, the first element in `$source` that passes the test specified by `$predicate`.
     */
    public static function FirstOrDefault(iterable $source, ?callable $predicate, mixed $defaultValue, mixed ...$params) : mixed
    {
        $index = 0;
        foreach ($source as $key => $element)
        {
            if ($predicate === null || $predicate($key, $element, ...$params))
            {
                return $element;
            }
            $index++;
        }
        return $defaultValue;
    }

    /**
     * Creates a generator that yields items from an iterable.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source Source sequence.
     * 
     * @return \Generator<TKey,TSource> A generator that yields elements from the source iterable.
     * 
     */
    public static function Generate(iterable $source) : \Generator
    {
        foreach ($source as $key => $item)
        {
            yield $key => $item;
        }
    }

    /**
     * Creates a generator that yields items from an iterable in reverse order.
     * 
     * This function does not make a reversed copy of the input sequence, so is suitable for large sequences.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source Source sequence.
     * 
     * @return \Generator<TKey,TSource> A generator that yields elements from the source iterable.
     * 
     */
    public static function GenerateReverse(iterable $source) : \Generator
    {
        for (end($source); ($key = key($source)) !== null; prev($source))
        {
            yield $key => $source[$key];
        }
    }

    /**
     * Groups the elements of a sequence according to a specified key selector function, with the elements of each group optionally projected by a specified value selector function.
     * 
     * This function allows you to group elements based on a key selector function, and optionally also project the values in each group using a value selector function.
     * The group key is passed to the value selector function so that different projections may be accomplished depending on the group key.
     * 
     * @template TSourceKey The type of the keys in the source sequence.
     * @template TSourceValue The type of the elements in the source sequence.
     * @template TKeyParam The type of the additional parameter passed to the key selector function.
     * @template TGroupKey The type of the group keys.
     * @template TGroupValue The type of the elements in the groups.
     * @template TValueParam The type of the additional parameter passed to the value selector function.
     * 
     * @param iterable<TSourceKey,TSourceValue> $source The source sequence.
     * @param callable(int $index, TSourceKey $key, TSourceValue $elem, TKeyParam $param): TGroupKey $keySelector A function to extract the key for each element.
     * The first parameter is the numeric index of the element in the source sequence. The second parameter is the element's key in the source sequence. The third parameter is the element value. The fourth parameter is the value of `$keySelectorParam`.
     * @param TKeyParam $keySelectorParam An additional value to be passed to the key selector function.
     * @param ?callable(TGroupKey $groupKey, int $index, TSourceKey $key, TSourceValue $elem, TKeyParam $param): TGroupValue $valueSelector A function to extract the value for each element.
     * The first parameter is the group key for this element selected by `$keySelector`. The second parameter is the numeric index of the element in the source sequence. The third parameter is the element's key in the source sequence. The fourth parameter is the element value. The fifth parameter is the value of `$valueSelectorParam`.
     * @param TValueParam $valueSelectorParam An additional value to be passed to the value selector function.
     * 
     * @return iterable<TGroupKey,TGroupValue[]> A sequence of groups containing the grouped elements.
     */
    public static function GroupBy(iterable $source, callable $keySelector, mixed $keySelectorParam = null, ?callable $valueSelector = null, mixed $valueSelectorParam = null) : array
    {
        $index = 0;
        $groups = [];
        if ($valueSelector === null)
        {
            // default selector maps the element to itself
            $valueSelector = fn($g,$i,$k,$e,$p) => $e;
        }
        foreach ($source as $key => $item)
        {
            $groupKey = $keySelector($index, $key, $item, $keySelectorParam);
            $value = $valueSelector($groupKey, $index, $key, $item, $valueSelectorParam);
            if (!array_key_exists($groupKey, $groups))
            {
                $groups[$groupKey] = [];
            }
            array_push($groups, $value);
            $index++;
        }
        return $groups;
    }

    /**
     * Determines whether a sequence contains any elements.
     * 
     * @param iterable $source The source sequence.
     * 
     * @return bool True if the sequence contains no elements, otherwise false.
     */
    public static function IsEmpty(iterable $source) : bool
    {
        return count($source) == 0;
    }

    /**
     * Returns the last element of the sequence that satisfies a condition, or a specified default value if no such element is found.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param ?callable(int $index, TKey $key, TSource $elem, mixed $params...):bool $predicate A function to test each element for a condition, or null if all elements are to be accepted. The index value passed to this function represents the position of the element in reverse order, i.e. 0 is the last element in the sequence.
     * @param TSource $defaultValue The default value to return if the sequence is empty or if no element passes the test specified by `$predicate`.
     * @param mixed ...$params Additional parameters to be passed to `$predicate`.
     * 
     * @return TSource `$defaultValue` if `$source` is empty or if no element passes the test specified by `$predicate`; otherwise, the last element in `$source` that passes the test specified by `$predicate`.
     */
    public static function LastOrDefault(iterable $source, ?callable $predicate, mixed $defaultValue, mixed ...$params) : mixed
    {
        $index = 0;
        $reverse = self::GenerateReverse($source);
        foreach ($reverse as $key => $element)
        {
            if ($predicate === null || $predicate($key, $element, ...$params))
            {
                return $element;
            }
            $index++;
        }
        return $defaultValue;
    }

    /**
     * Projects each element of an array into a new form incorporating the element's index and key.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source array.
     * @template TResult The type of the elements in the result array.
     * 
     * @param iterable<TKey,TSource> $source The source array to invoke a transform function on.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params): TResult $transform A transform function to apply to each source element. The first argument is the index of the element (starting with 0). The second argument is the element's key from the source array. The third argument is the element. All remaining arguments come from $selectorParams.
     * @param mixed ...$transformParams Additional parameters to be passed to the transform function.
     * 
     * @return TResult[] An array whose elements are the result of invoking the transform function on each element of the source array.
     */
    public static function Map(iterable $source, callable $transform, ...$transformParams) : array
    {
        return iterator_to_array(self::MapLazy($source, $transform, ...$transformParams));
    }

    /**
     * Projects each element of an array into a new form incorporating the element's index and key.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source array.
     * @template TResult The type of the elements in the result array.
     * 
     * @param iterable<TKey,TSource> $source The source array to invoke a transform function on.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params): TResult $transform A transform function to apply to each source element. The first argument is the index of the element (starting with 0). The second argument is the element's key from the source array. The third argument is the element. All remaining arguments come from $selectorParams.
     * @param mixed ...$transformParams Additional parameters to be passed to the transform function.
     * 
     * @return \Generator<mixed,TResult> An generator that produces elements which are the result of invoking the transform function on each element of the source array.
     */
    public static function MapLazy(iterable $source, callable $transform, mixed ...$transformParams) : \Generator
    {
        $index = 0;
        foreach ($source as $key => $value)
        {
            yield $transform($index, $key, $value, ...$transformParams);
            $index++;
        }
    }

    /**
     * Creates an associative array from a sequence according to a specified key selector function, and optionally a value selector function.
     * 
     * If the key selector returns the same key more than once, only the last element with that key will appear in the returned array.
     * 
     * @template TSourceKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * @template TKey The type of the key in the resulting array.
     * @template TKeyParam The type of the parameter passed to the key selector.
     * @template TVal The type of the elements in the resulting array.
     * @template TValParam The type of the parameter passed to the value selector.
     * 
     * @param iterable<TSourceKey,TSource> $source The source array to create the associative array from.
     * @param callable(TSource $elem):TKey|callable(int $index, TSourceKey $key, TSource $elem, TKeyParam $param):TKey $keySelector A function to extract a key from each element.
     * @param ?TKeyParam $keySelectorParam A parameter to be passed to the $keySelector function. If this is null, no second parameter is passed.
     * @param ?callable(TSource $elem):TVal|?callable(int $index, TSourceKey $oldKey, TKey $newKey, TSource $elem, TValParam $param):TVal $valueSelector A function to transform each element. If null, the values from the input array are used as-is.
     * @param ?TValParam $valueSelectorParam A parameter to be passed to the $valueSelector function. If this is null, no second parameter is passed.
     * 
     * @return iterable<TKey,TVal> An associative array that contains values selected from the input array and keys selected by the key selector function. 
     */
    public static function MapAssociative(iterable $source, callable $keySelector, mixed $keySelectorParam = null, ?callable $valueSelector = null, mixed $valueSelectorParam = null) : array
    {
        $results = [];
        $generator = self::MapAssociativeLazy($source, $keySelector, $keySelectorParam, $valueSelector, $valueSelectorParam);
        return iterator_to_array($generator);
    }

    /**
     * Creates an associative array from a sequence according to a specified key selector function, and optionally a value selector function, using a generator for lazy execution.
     * 
     * If the key selector returns the same key more than once, only the last element with that key will appear in the returned array.
     * 
     * @template TSourceKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * @template TKey The type of the key in the resulting array.
     * @template TKeyParam The type of the parameter passed to the key selector.
     * @template TVal The type of the elements in the resulting array.
     * @template TValParam The type of the parameter passed to the value selector.
     * 
     * @param iterable<TSourceKey,TSource> $source The source array to create the associative array from.
     * @param callable(TSource $elem):TKey|callable(int $index, TSourceKey $key, TSource $elem, TKeyParam $param):TKey $keySelector A function to extract a key from each element.
     * @param ?TKeyParam $keySelectorParam A parameter to be passed to the $keySelector function. If this is null, no second parameter is passed.
     * @param ?callable(TSource $elem):TVal|?callable(int $index, TSourceKey $oldKey, TKey $newKey, TSource $elem, TValParam $param):TVal $valueSelector A function to transform each element. If null, the values from the input array are used as-is.
     * @param ?TValParam $valueSelectorParam A parameter to be passed to the $valueSelector function. If this is null, no second parameter is passed.
     * 
     * @return \Generator<TKey,TVal> An associative array that contains values selected from the input array and keys selected by the key selector function. 
     */
    public static function MapAssociativeLazy(iterable $source, callable $keySelector, mixed $keySelectorParam = null, ?callable $valueSelector = null, mixed $valueSelectorParam = null) : \Generator
    {
        if ($valueSelector === null)
        {
            // default value selector is a direct map to the element value
            $valueSelector = fn($i,$kold,$knew,$v,$p) => $v;
        }
        $index = 0;
        foreach ($source as $key => $value)
        {
            $newKey = $keySelector($index, $key, $value, $keySelectorParam);
            $newValue = $valueSelector($index, $key, $newKey, $value, $valueSelectorParam);
            yield $newKey => $newValue;
            $index++;
        }
    }

    /**
     * Returns the maximum value in a sequence according to a specified selector function.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence to determine the maximum value of.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params):(int|float|null) $selector A function to extract the value for each element. If the function returns null, the element is ignored.
     * @param mixed ...$selectorParams Additional parameters to be passed to the selector function.
     * 
     * @return int|float|null The maximum value extracted from the sequence.
     */
    public static function MaxBy(iterable $source, callable $selector, mixed ...$selectorParams) : int|float|null
    {
        $max = null;
        $index = 0;
        foreach ($source as $key => $item)
        {
            $value = $selector($index, $key, $item, ...$selectorParams);
            if ($max === null)
            {
                $max = $value;
            }
            else if ($value === null)
            {
                continue;
            }
            {
                $max = max($max, $value);
            }
            $index++;
        }
        return $max;
    }

    /**
     * Merges associative sequences together into a single sequence.
     * 
     * If the same key appears more than once in the source sequences, the value from the latest sequence is preserved.
     * 
     * @template TKey The type of the keys in the source sequences.
     * @template TSource The type of the elements in the source sequences.
     * 
     * @param iterable<TKey,TSource> ...$sources The source sequences.
     * 
     * @return \Generator<TKey,TSource> The concatenated sequence.
     */
    public static function MergeAssociative(iterable ...$sources) : \Generator
    {
        foreach ($sources as $source)
        {
            foreach ($source as $key => $elem)
            {
                yield $key => $elem;
            }
        }
    }

    /**
     * Returns the minimum value in a sequence according to a specified selector function.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params):(int|float|null) $selector A function to extract the value for each element. If the function returns null, the element is ignored.
     * @param mixed ...$selectorParams Additional parameters to be passed to the selector function.
     * 
     * @return int|float The minimum value extracted from the sequence.
     */
    public static function MinBy(iterable $source, callable $selector, mixed ...$selectorParams) : int|float|null
    {
        $min = null;
        $index = 0;
        foreach ($source as $key => $item)
        {
            $value = $selector($index, $key, $item, ...$selectorParams);
            if ($min === null)
            {
                $min = $value;
            }
            else if ($value === null)
            {
                continue;
            }
            {
                $min = min($min, $value);
            }
            $index++;
        }
        return $min;
    }

    /**
     * Sorts the elements of a sequence in ascending order according to a specified comparison function.
     * 
     * @template TKey The types of the keys in the source sequence.
     * @template TSource The types of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(TValue $a, TValue $b, mixed ...$params):int $comparer A function that compares two elements and returns less than, equal to, or greater than zero if the first element is considered to be respectively less than, equal to, or greater than the second.
     * @param mixed ...$comparerParams Additional parameters to be passed to the comparison function.
     * 
     * @return TSource[] A sequence of values sorted in ascending order based on the results of the comparisons.
     */
    public static function SortByAscendingValue(iterable $source, callable $comparer, mixed ...$comparerParams) : array
    {
        $array = array(...$source);

        usort($array, function($a, $b) use ($comparer, $comparerParams) : int {
            // execute the comparer with the specified parameters
            return $comparer($a, $b, ...$comparerParams);
        });

        return $array;
    }

    /**
     * Sorts the elements of a sequence in descending order according to a specified comparison function.
     * 
     * @template TKey The types of the keys in the source sequence.
     * @template TSource The types of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(TValue $a, TValue $b, mixed ...$params):int $comparer A function that compares two elements and returns less than, equal to, or greater than zero if the first element is considered to be respectively less than, equal to, or greater than the second.
     * @param mixed ...$comparerParams Additional parameters to be passed to the comparison function.
     * 
     * @return TSource[] A sequence of values sorted in descending order based on the results of the comparisons.
     */
    public static function SortByDescendingValue(iterable $source, callable $comparer, mixed ...$comparerParams) : array
    {
        $array = array(...$source);

        usort($array, function($a, $b) use ($comparer, $comparerParams) : int {
            // execute the comparer with the specified parameters, negating the results
            return -$comparer($a, $b, ...$comparerParams);
        });
        
        return $array;
    }

    /**
     * Sorts the elements of a sequence in ascending order according to a specified comparison function.
     * 
     * @template TKey The types of the keys in the source sequence.
     * @template TSource The types of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(TValue $a, TValue $b):int ...$comparers One or more comparison functions that compare two elements and returns less than, equal to, or greater than zero if the first element is considered to be respectively less than, equal to, or greater than the second.
     * Each comparison function is tested in sequence, resulting in the sequence being sorted by the first criteria, then any equally ranked elements sorted by the second criteria, and so forth.
     * 
     * @return TSource[] A sequence of values sorted in ascending order based on the results of the comparisons.
     */
    public static function SortByMultipleAscendingValue(iterable $source, callable ...$comparers) : array
    {
        $array = array(...$source);

        $comparison = function($a, $b) use ($comparers) {
            // test each comparer in sequence until a nonzero rank is returned
            foreach ($comparers as $comparer)
            {
                $rank = $comparer($a, $b);
                if ($rank !== 0)
                {
                    return $rank;
                }
            }
            return 0;
        };

        usort($array, $comparison);

        return $array;
    }

    /**
     * Sorts the elements of a sequence in descending order according to a specified comparison function.
     * 
     * @template TKey The types of the keys in the source sequence.
     * @template TSource The types of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(TValue $a, TValue $b):int ...$comparers One or more comparison functions that compare two elements and returns less than, equal to, or greater than zero if the first element is considered to be respectively less than, equal to, or greater than the second.
     * Each comparison function is tested in sequence, resulting in the sequence being sorted by the first criteria, then any equally ranked elements sorted by the second criteria, and so forth.
     * 
     * @return TSource[] A sequence of values sorted in descending order based on the results of the comparisons.
     */
    public static function SortByMultipleDescendingValue(iterable $source, callable ...$comparers) : array
    {
        $array = array(...$source);

        $comparison = function($a, $b) use ($comparers) {
            // test each comparer in sequence until a nonzero rank is returned
            foreach ($comparers as $comparer)
            {
                $rank = -$comparer($a, $b);
                if ($rank !== 0)
                {
                    return $rank;
                }
            }
            return 0;
        };

        usort($array, $comparison);

        return $array;
    }

    /**
     * Prepends a value to the start of a sequence.
     * 
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<mixed,TSource> $source The source sequence.
     * @param TSource $value The value to prepend.
     * 
     * @return \Generator<mixed,TSource>
     */
    public static function Prepend(iterable $source, mixed $value) : \Generator
    {
        yield $value;
        foreach ($source as $elem)
        {
            yield $elem;
        }
    }

    /**
     * Computes the multiplicative product of values projected from the input sequence according to a value selector function.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params):(int|float) $valueSelector A transform function to apply to each element.
     * @param mixed ...$selectorParams Additional parameters to be passed to the transform function.
     * 
     * @return int|float|null The multiplicative product of the projected values.
     */
    public static function Product(iterable $source, callable $valueSelector, int|float $initialValue, mixed ...$selectorParams) : int|float|null
    {
        $accumulator = $initialValue;
        $index = 0;
        foreach ($source as $key => $value)
        {
            $accumulator *= $valueSelector($index, $key, $value, ...$selectorParams);
            $index++;
        }
        if ($index === 0)
        {
            return null;
        }
        return $accumulator;
    }

    /**
     * Generates a sequence that contains one repeated value.
     * 
     * @template TElement The type of the elements in the resulting sequence.
     * 
     * @param TElement $value The value to be repeated.
     * @param int $count The number of times to repeat the value in the generated sequence.
     * 
     * @return TElement[] A generator that produces the repeated value.
     */
    public static function Repeat(mixed $value, int $count) : array
    {
        $generator = self::RepeatLazy($value, $count);
        return iterator_to_array($generator);
    }

    /**
     * Generates a sequence that contains one repeated value.
     * 
     * @template TElement The type of the elements in the resulting sequence.
     * 
     * @param TElement $value The value to be repeated.
     * @param int $count The number of times to repeat the value in the generated sequence.
     * 
     * @return \Generator<int,TElement> A generator that produces the repeated value.
     */
    public static function RepeatLazy(mixed $value, int $count) : \Generator
    {
        if ($count < 0)
        {
            throw new \InvalidArgumentException('Count must be greater than or equal to zero.');
        }

        for ($i = 0; $i < $count; $i++)
        {
            yield $i => $value;
        }
    }

    /**
     * Determines whether two sequences are equal according to an equality comparer.
     * 
     * @template TFirstKey The type of the keys in first source sequence.
     * @template TFirst The type of the elements in the first source sequence.
     * @template TSecondKey The type of the keys in second source sequence.
     * @template TSecond The type of the elements in the second source sequence.
     * 
     * @param iterable<TFirstKey,TFirst> $source The source sequence.
     * @param iterable<TSecondKey,TSecond> $second A sequence to compare to the first sequence.
     * @param callable(int $index, TFirstKey $firstKey, TFirst $first, TSecondKey $secondKey, TSecond $second, mixed ...$params) : bool A comparison function to compare elements.
     * @param mixed ...$comparerParams Additional parameters to be passed to the comparison function.
     * 
     * @return bool True if the two sequences are of equal length and their corresponding elements compare equal according to `$comparer`, otherwise false.
     */
    public static function SequenceEqual(iterable $first, iterable $second, callable $comparer, mixed ...$comparerParams) : bool
    {
        $firstGenerator = self::Generate($first);
        $secondGenerator = self::Generate($second);
        if (iterator_count($first) != iterator_count($second))
        {
            return false;
        }
        $index = 0;
        while ($firstGenerator->valid() && $secondGenerator->valid())
        {
            $firstValue = $firstGenerator->current();
            $firstKey = $firstGenerator->key();
            $secondValue = $secondGenerator->current();
            $secondKey = $secondGenerator->key();
            
            if (!$comparer($index, $firstKey, $firstValue, $secondKey, $secondValue, ...$comparerParams))
            {
                return false;
            }
            $firstGenerator->next();
            $secondGenerator->next();
            $index++;
        }
        return true;
    }

    /**
     * Bypasses a specified number of elements in a sequence then returns the remaining elements.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TValue The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $count The number of elements to skip before returning the remaining elements.
     * 
     * @return \Generator<TKey,TSource> A generator that produces the elements that occur after the specified index in the input sequence.
     */
    public static function Skip(iterable $source, int $count) : \Generator
    {
        if ($count < 0)
        {
            throw new \InvalidArgumentException("Count must be greater than or equal to zero.");
        }

        $index = 0;
        foreach ($source as $key => $value)
        {
            if ($index >= $count)
            {
                yield $key => $value;
            }
            $index++;
        }
    }

    /**
     * Computes the sum of values projected from the input sequence according to a value selector function.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TSource The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $elem, mixed ...$params):(int|float) $valueSelector A transform function to apply to each element.
     * @param mixed ...$params Additional parameters to be passed to the transform function.
     * 
     * @return int|float|null The sum of the projected values, or null if the sequence contains no elements.
     */
    public static function Sum(iterable $source, callable $valueSelector, mixed ...$params) : int|float|null
    {
        $accumulator = 0;
        $index = 0;
        foreach ($source as $key => $value)
        {
            $accumulator += $valueSelector($index, $key, $value, ...$params);
            $index++;
        }
        if ($index === 0)
        {
            return null;
        }
        return $accumulator;
    }

    /**
     * Returns a specified number of contiguous elements from the start of a sequence.
     * 
     * If fewer than the requested number of elements are present, fewer elements are returned.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TValue The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $count The number of elements to return from the source sequence.
     * 
     * @return \Generator<TKey,TSource> A generator that produces the requested number of elements from the source sequence.
     */
    public static function Take(iterable $source, int $count) : \Generator
    {
        if ($count < 0)
        {
            throw new \InvalidArgumentException("Count must be greater than or equal to zero.");
        }

        $returned = 0;
        foreach ($source as $key => $value)
        {
            if ($returned >= $count)
                break;

            yield $key => $value;
            $returned++;
        }
    }

    /**
     * Returns a specified number of contiguous elements from the start of a sequence.
     * 
     * If fewer than the requested number of elements are present, fewer elements are returned.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TValue The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param callable(int $index, TKey $key, TSource $source, mixed ...$params): bool $predicate A function to test each source element for a condition.
     * @param mixed ...$params Additional parameters to be passed to the predicate.
     * 
     * @return \Generator<int,TSource> A generator that produces the requested number of elements from the source sequence.
     */
    public static function TakeWhile(iterable $source, callable $predicate, mixed ...$params) : \Generator
    {
        $index = 0;
        foreach ($source as $key => $value)
        {
            if (!$predicate($index, $key, $value))
                break;
            yield $key => $value;
            $index++;
        }
    }

    /**
     * Returns a specified number of contiguous elements from the end of a sequence.
     * 
     * If fewer than the requested number of elements are present, fewer elements are returned.
     * 
     * @template TKey The type of the keys in the source sequence.
     * @template TValue The type of the elements in the source sequence.
     * 
     * @param iterable<TKey,TSource> $source The source sequence.
     * @param int $count The number of elements to return from the source sequence.
     * @return \Generator<TKey,TSource> A generator that produces the requested number of elements from the end of the source sequence.
     */
    public static function TakeLast(iterable $source, int $count) : \Generator
    {
        if ($count < 0)
        {
            throw new \InvalidArgumentException("Count must be greater than or equal to zero.");
        }

        // move the array pointer to the last element of the array
        end($source);
        // step at most $count - 1 entries back
        for ($i = 0; $i < $count - 1; $i++)
        {
            if (prev($source) === false)
            {
                break;
            }
        }
        // yield the rest
        for (; ($key = key($source)) !== null; next($source))
        {
            yield $key => $source[$key];
        }
    }

    /**
     * Applies a specified function to the corresponding element of two sequences, producing a sequence of the results.
     * 
     * This function merges elements based on their positions within the sequence, ignoring keys. If the two sequences do not have the same number of elements, this function will merge them until it reaches the end of the shortest sequence.
     * 
     * @template TFirstKey The type of the keys in first source sequence.
     * @template TFirst The type of the elements in the first source sequence.
     * @template TSecondKey The type of the keys in second source sequence.
     * @template TSecond The type of the elements in the second source sequence.
     * @template TResult The type of the elements in the result sequence.
     * 
     * @param iterable<TFirstKey,TFirst> $first The first sequence.
     * @param iterable<TSecondKey,TSecond> $second The second sequence.
     * @param callable(int $index, TFirstKey $firstKey, TFirst $first, TSecondKey $secondKey, TSecond $second, mixed ...$params): TResult $resultSelector A function that specifies how to merge the elements from the two sequences.
     * @param mixed ...$params Additional parameters to be passed to the result selector function.
     * 
     * @return TResult[] An array that contains the merged elements of the two input sequences.
     */
    public static function Zip(iterable $first, iterable $second, callable $resultSelector, mixed ...$params) : array
    {
        return iterator_to_array(self::ZipLazy($first, $second, $resultSelector, ...$params));
    }

    /**
     * Applies a specified function to the corresponding elements of two sequences, producing a lazily generated sequence of the results.
     * 
     * This function merges elements based on their positions within the array, ignoring keys. If the two arrays do not have the same number of elements, this function will merge them until it reaches the end of the shortest array.
     * 
     * @template TFirstKey The type of the keys in first source sequence.
     * @template TFirst The type of the elements in the first source sequence.
     * @template TSecondKey The type of the keys in second source sequence.
     * @template TSecond The type of the elements in the second source sequence.
     * @template TResult The type of the elements in the result array.
     * 
     * @param \Generator<TFirstKey,TFirst>|TFirst[] $first The first array.
     * @param \Generator<TSecondKey,TSecond>|TSecond[] $second The second array.
     * @param callable(int $index, TFirstKey $firstKey, TFirst $first, TSecondKey $secondKey, TSecond $second, mixed ...$params): TResult $resultSelector A function that specifies how to merge the elements from the two arrays.
     * @param mixed ...$params Additional parameters to be passed to the result selector function.
     * 
     * @return \Generator<int,TResult> A generator that produces the merged elements of the two input arrays.
     */
    public static function ZipLazy(\Generator|array $first, \Generator|array $second, callable $resultSelector, mixed ...$params) : \Generator
    {
        $firstGenerator = null;
        $secondGenerator = null;
        if (is_array($first))
        {
            $firstGenerator = self::Generate($first);
        }
        else
        {
            $firstGenerator = $first;
        }
        if (is_array($second))
        {
            $secondGenerator = self::Generate($second);
        }
        else
        {
            $secondGenerator = $second;
        }
        $index = 0;
        while ($firstGenerator->valid() && $secondGenerator->valid())
        {
            /** @var TFirst $firstValue; */
            $firstValue = $firstGenerator->current();
            $firstKey = $firstGenerator->key();
            $secondValue = $secondGenerator->current();
            $secondKey = $secondGenerator->key();
            yield $resultSelector($index, $firstKey, $firstValue, $secondKey, $secondValue, ...$params);

            $firstGenerator->next();
            $secondGenerator->next();
            $index++;
        }
    }
}

?>
