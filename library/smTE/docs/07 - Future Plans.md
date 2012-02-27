# Future Plans

## Improve unit test suite

Would need to be changed to integrate into whatever systems we need. Right
now, just a quick and dirty one.

**Benefits:**

- Easier integration with continuous integration.
- Better notification of failures, correct errors, etc. possibly?
- Testing on performance changes of unit tests.

**Drawbacks:**

- Might be more work to write tests.


## Measure performance in tests

Even if this is a simple "shouldn't take longer than 100ms" or something, there
should be some sort of performance regression test.

An ideal system would log performance across test runs and show how performance
changes.

**Benefits:**

- Will help to identify performance regressions or improvements.
- Can serve as a poor man's profiling for what to concentrate on.

**Drawbacks:**

- Could be a lot of work to implement right.


## Optimization and profiling

Optimization and profiling of the compilation process needs to be done to make
sure there are no obvious bottlenecks or major areas for performance gain.
Even though it's meant for the output to be cached, spending at least some time
will improve things for developers, and prevent any serious performance flaws.

**Benefits:**

- Will ensure there are no major performance problems in system.

**Drawbacks:**

- Can take a lot of time to do accurately and usefully.
- Won't affect most people since the compiler won't run often.


## Output optimization and inlining

There are many areas for improvement, such as inlining simple/obvious template
calls, or similar. It might also be worthwhile to remove dead code
automatically, (statically false if conditions, etc.) and evaluate obvious
expressions at compile time.

Some of that is definitely overboard, but it should be considered and weighed.

**Benefits:**

- Could save memory storage.
- Should save at least some time.
- Will affect everyone who uses the system greatly.

**Drawbacks:**

- Can be a lot of work, especially the more major portions.
- Performance benefit/difference may be extremely small.
- Need to do some profiling/benchmarking to know what to worry about.