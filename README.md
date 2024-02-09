PINQ is a collection of array/iterable manipulation functions for PHP, inspired by LINQ in .NET - more specifically by the [`System.Linq.Enumerable`](https://learn.microsoft.com/en-us/dotnet/api/system.linq.enumerable) methods.

I wrote this to lessen the cognitive burden of working with PHP's inbuilt array manipulation functions, and to minimise the number of times I needed to re-write implementations of this functionality inline in my apps.

PINQ currently implements `All`, `Any`, `Append`, `Average`, `CategoriseBy`, `Chunk`, `ChunkLazy`, `Concat`, `ContainsAtLeast`, `ContainsNoMoreThan`, `Count`, `FirstOrDefault`, `Generate`, `GenerateReverse`, `GroupBy`, `IsEmpty`, `LastOrDefault`, `Map`, `MapLazy`, `MapAssociative`, `MapAssociativeLazy`, `MaxBy`, `MergeAssociative`, `MinBy`, `SortByAscendingValue`, `SortByDescendingValue`, `SortByMultipleAscendingValue`, `SortByMultipleDescending`, `Prepend`, `Product`, `Repeat`, `RepeatLazy`, `SequenceEqual`, `Skip`, `Sum`, `Take`, `TakeLast`, `Zip`, and `ZipLazy`.

## Things PINQ is:

 - A collection of useful array/iterable manipulation functions for PHP.
 - Generally more descriptive than PHP's inbuilt array manipulation functions.
 - Pretty well documented. The phpdoc comments allow for strong type inference when using a suitable IDE (e.g. vscode + intelephense)
 - Something I wrote over the course of two evenings.
 - Probably a good starting point if you want to put some work in writing unit tests to find the bugs.

## Things PINQ is **NOT**:

 - Production ready. PINQ SHOULD NOT BE USED IN PRODUCTION. **DO NOT USE THIS IN PRODUCTION.**
 - Well tested. This is a low-effort, poorly tested implementation. I wrote a handful of unit tests but the coverage is very small.
 - Guaranteed to be correct. See above.
 - Fast. Approximately zero consideration was put into performance.
 - A port of the LINQ API surface or method behaviour. PINQ is inspired by LINQ, it is not a direct port. Some things are done differently.
 - Production ready. I'm serious. Don't use this for anything that matters. HAVE I SAID THIS ENOUGH YET?
 - Actively maintained. I have ADHD, I don't do "actively maintained". I work on things for a couple of days and get bored. Fork 'em if you got 'em.

## Theoretically Asked Questions:

 - "Can I use this in production?" only if you like production being on fire, and also getting fired.
 - "Why use `$params` instead of `use(...)` for closure variable capture?" I genuinely didn't know PHP had that feature until 80% of the way through writing this, but this does mean you can also just directly pass native functions too.
 - "It's been 6 months and you haven't answered my issue" that's not a question but I direct you to my aforementioned ADHD and PINQ not being actively maintained.
 - "Why did you do \[x\] this way?" because I wrote this in 2 evenings and the code was pretty much a stream of consciousness.
 - "Can you implement \[some function\]?" I am probably theoretically capable.
 - "Ok, *will* you implement \[some function\]?" There is very little chance that this will happen in practice.
 - ":(" look, my brain is basically a puppy that chugged six cans of red bull in a squirrel factory, and just I want to make it suuuuuper clear that the chances of me responding to literally anything about this approaches zero.
