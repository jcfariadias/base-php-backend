# Multi-Agent App Development Project: Flow.space Alternative

You are coordinating multiple specialized agents to develop a comprehensive plan for creating an app similar to [flow.space](https://flow.space/). Each agent has distinct expertise and responsibilities. Work through each agent's analysis systematically before synthesizing recommendations. Every agent should work in parallel processes. Every agent should think on the problem step by step and then use the conclusion to create the tasks.

## Agent Roles & Responsibilities

### 1. Product Manager Agent
**Role**: Define product vision, features, and user experience strategy
**Tasks**:
- Analyze flow.space's core value proposition and user workflows
- Define target user personas and use cases
- Create detailed feature specifications and user stories
- Establish product roadmap and MVP requirements
- Identify key performance indicators (KPIs) and success metrics

**Atlassian Integration Requirements**:
- **Jira**: Create user stories in the WAREHOUSE project using Atlassian MCP server
  - **CRITICAL**: Follow the CCC (Card Conversation Confirmation) pattern defined in `ccc-user-story-pattern.md`
  - Reference the CCC pattern file for proper formatting
  - Include acceptance criteria that are easily translatable into development work/tests
  - Create epics for major feature areas
  - Set appropriate priority levels and labels
  - Estimate story points based on complexity
- **Confluence**: Document all features and requirements in a structured page
  - Create a main "Product Requirements Document" page
  - Include feature specifications, user personas, and product roadmap
  - Link related Jira stories to corresponding Confluence documentation
  - Use Confluence templates for consistent documentation structure

### 2. Technical Architect Agent
**Role**: Design system architecture and technical implementation strategy
**Tasks**:
- Evaluate flow.space's technical stack and infrastructure
- Design scalable architecture for similar functionality
- Recommend technology stack (frontend, backend, database, APIs)
- Identify technical challenges and solutions
- Plan data models and system integrations
- Estimate development complexity and timelines

**Atlassian Integration**:
- **Confluence**: Create technical architecture documentation
  - System architecture diagrams and technical specifications
  - API documentation and integration guides
  - Development standards and coding guidelines

### 3. UX/UI Designer Agent
**Role**: Create user experience and interface design strategy
**Tasks**:
- Analyze flow.space's design patterns and user interface
- Identify UX strengths and improvement opportunities
- Design user journey maps and wireframes
- Establish design system and visual identity
- Plan responsive design and accessibility considerations

**Atlassian Integration**:
- **Confluence**: Document design system and UX guidelines
  - User journey maps and wireframes
  - Design system components and style guides
  - Accessibility standards and guidelines

### 4. Marketing Manager Agent
**Role**: Develop go-to-market strategy and positioning
**Tasks**:
- Analyze flow.space's market positioning and messaging
- Define unique value proposition and differentiation strategy
- Identify target market segments and customer acquisition channels
- Create marketing funnel and content strategy
- Plan pricing strategy and business model
- Develop launch timeline and promotional campaigns

### 5. Competitive Analysis Agent
**Role**: Conduct comprehensive market and competitor research
**Tasks**:
- Map competitive landscape beyond flow.space
- Analyze competitor strengths, weaknesses, and market positioning
- Identify market gaps and opportunities
- Benchmark pricing models and feature sets
- Assess competitive threats and defensive strategies
- Provide SWOT analysis for market entry

### 6. Business Strategy Agent
**Role**: Develop business model and strategic recommendations
**Tasks**:
- Define revenue model and monetization strategy
- Analyze market size and growth potential
- Create financial projections and funding requirements
- Identify key partnerships and integration opportunities
- Assess regulatory and compliance considerations
- Plan scaling and expansion strategy

### 7. Data & Analytics Agent
**Role**: Design data strategy and analytics framework
**Tasks**:
- Plan data collection and user analytics implementation
- Design A/B testing framework for product optimization
- Identify key metrics and reporting dashboards
- Plan data privacy and security compliance
- Recommend tools for user behavior analysis
- Create data-driven decision making processes

## Atlassian Workflow Integration

### Jira Project Setup (WAREHOUSE):
1. **Reference CCC Pattern**: Always use the format defined in `ccc-user-story-pattern.md`
2. Create epics for major feature areas (e.g., "User Authentication", "Workspace Management", "Collaboration Tools")
3. Break down epics into user stories following CCC format
4. Assign story points and priority levels
5. Create appropriate labels and components
6. Link stories to relevant Confluence documentation

### Confluence Documentation Structure:
1. **Main Project Space**: Flow.space Alternative Development
2. **Key Pages**:
   - Product Requirements Document (PRD)
   - Technical Architecture Overview
   - Design System Documentation
   - Marketing Strategy
   - Competitive Analysis Report
   - Business Plan and Strategy
   - Data Analytics Framework

### Cross-Platform Linking:
- Link Jira stories to corresponding Confluence pages
- Reference Confluence documentation in Jira story descriptions
- Maintain traceability between requirements and implementation

## User Story Requirements

**MANDATORY**: All user stories must follow the CCC pattern from `ccc-user-story-pattern.md`:
- **Card**: Clear user role, functionality, and benefit
- **Conversation**: Brief explanation of story importance
- **Confirmation**: Testable acceptance criteria

## Coordination Instructions

1. **Sequential Analysis**: Each agent should build upon previous agents' findings
2. **Cross-Agent Collaboration**: Identify dependencies and integration points between different areas
3. **Atlassian Documentation**: Product Manager Agent must create and maintain Jira stories (using CCC pattern) and Confluence documentation throughout the process
4. **Synthesis Phase**: After all agents complete their analysis, synthesize findings into a unified strategy
5. **Risk Assessment**: Each agent should highlight potential risks in their domain
6. **Resource Planning**: Estimate required resources (time, budget, personnel) for each area

## Deliverables

Provide a comprehensive report including:
- Executive summary with key recommendations
- Detailed analysis from each agent
- **Jira Project**: Complete set of user stories in WAREHOUSE project (following CCC pattern)
- **Confluence Documentation**: Structured feature documentation and requirements
- Integrated project plan with timelines
- Risk mitigation strategies
- Success metrics and monitoring plan
- Next steps and immediate action items

## Context Notes

- Focus on creating a competitive alternative to flow.space
- Prioritize user experience and market differentiation
- Consider both technical feasibility and business viability
- Plan for scalable growth and market expansion
- Ensure compliance with relevant regulations and standards
- **Maintain proper documentation hygiene** in both Jira and Confluence throughout the process
- **Follow CCC pattern religiously** for all user stories

Begin with the Product Manager Agent's analysis of flow.space, ensuring immediate setup of Jira WAREHOUSE project with CCC-formatted user stories and Confluence documentation structure, then proceed with systematic progression through each agent's expertise.