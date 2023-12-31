# schmarty-gem-diamond

From discussions with Marty:


> Functionally, I have these ideas in mind for the future (which I should probably collect as a real roadmap):
> * [ ] Adding a real previous / next system to make it a "true ring". As mentioned earlier I like the idea of periodically shuffling the ring. It would be neat to have the directory reflect the ordering, as well.
> * [ ] Decide on, implement, and document when and how links are checked. Currently, gardening tests every registered site on the ring, and it only happens when someone nudges me in the IndieWeb chat or when I feel like it.
> * [ ] Small design improvements to make it easier to find the FAQ, directory page, etc.
> * [ ] Along with those, I'm interested in adding administration and moderation tools. These would probably include management pages on the webring site itself as well as feed(s) for site activity, possibly non-public, that I can follow in my reader.
> 
> Non-functionally, my goals are to keep the project small and understandable:
> * [ ] It's a project on which I can sharpen my PHP skills. I've been doing web development since the early 2000s and I work with PHP a lot in my day job, but that mostly involves a huge hairy legacy codebase full of non-modern patterns. I'd like to keep the codebase small and well-organized, but of course I am also learning what that means to me.
> * [ ] Scaling is an anti-goal. If this particular webring can't be hosted on a tiny VPS with SQLite as the database then it's time to shuffle on, haha.
> 
>With those things in mind, so far I am *quite* interested in what you're setting up for PHPUnit. Conversely, to me Docker feels "heavy" for this project.
> 
> Someday / maybe things that I consider outside the scope of the ring itself:
> * [ ] A "planet" that aggregates posts from members of the ring. (Risky due to moderation concerns.)
> * [ ] Continuing to refine Micropubkit, of which the webring is just one client. It's also the basis for a handful of Micropub clients at https://bayside.pub/. I'd very much like to get it to a point where it's a solid base for other folks to quickly slap together > Micropub clients (or other sites that use sign-in with IndieAuth).
> 