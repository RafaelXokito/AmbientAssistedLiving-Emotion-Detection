package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;

import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.*;

import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.*;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Emotion;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.*;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;

import java.util.ArrayList;
import java.util.List;
import java.util.stream.Collectors;

@Path("iterations")
@Produces({MediaType.APPLICATION_JSON})
@Consumes({MediaType.APPLICATION_JSON})
@RolesAllowed({"Client", "Administrator"})
public class IterationService {
    
    @EJB
    private IterationBean iterationBean;
    @EJB
    private PersonBean personBean;

    @Context
    private SecurityContext securityContext;

    @GET
    @Path("/")
    public Response getAllIterationsWS(@HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        
        if (securityContext.isUserInRole("Client"))
            return Response.status(Response.Status.OK)
                .entity(simpleToDTOs(iterationBean.getAllIterationsByClient(clientEmail)))
                .build();
                
        return Response.status(Response.Status.OK)
                .entity(simpleToDTOs(iterationBean.getAllIterations()))
                .build();
    }

    @GET
    @Path("{id}")
    public Response getIterationWS(@PathParam("id") Long id, @HeaderParam("Authorization") String auth) throws Exception {
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        Iteration iteration = iterationBean.findIteration(id);

        if (securityContext.isUserInRole("Client") && !iteration.getClient().getEmail().equals(clientEmail))
            throw new MyUnauthorizedException("You are not allowed to see this iteration");

        return Response.status(Response.Status.OK)
                .entity(toDTO(iteration))
                .build();
    }

    @GET
    @Path("/graphData")
    @Produces(MediaType.APPLICATION_JSON)
    public Response getGraphData(@HeaderParam("Authorization") String auth, @DefaultValue("HOURS") @QueryParam("pattern")  String pattern) throws Exception {
        List<Object[]> graphData;
        if(!pattern.equals("YEARMONTHDAY") && !pattern.equals("YEARMONTH") && !pattern.equals("YEAR") && !pattern.equals("MONTH")  && !pattern.equals("WEEKDAY") && !pattern.equals("HOURS")){
            throw new MyIllegalArgumentException("[Error] -  pattern is invalid");
        }
        switch (pattern){
            case "YEARMONTHDAY":
                pattern = "'yyyy-MM-DD'";
                break;
            case "YEARMONTH":
                pattern = "'yyyy-MM'";
                break;
            case "YEAR":
                pattern = "'yyyy'";
                break;
            case "MONTH":
                pattern = "'MM'";
                break;
            case "WEEKDAY":
                pattern = "'D'";
                break;
            default:
                pattern = "'HH24'";
                break;
        }
        if (securityContext.isUserInRole("Client")) {
            graphData = iterationBean.getCountIterationByDate(pattern, personBean.getPersonByAuthToken(auth).getId());
        }else{
            graphData = iterationBean.getCountIterationByDate(pattern, null);
        }
        return Response.status(Response.Status.OK).entity(graphData).build();
    }

    /*
    It's not possible to create or delete iterations (JUST DELETE)
    @POST
    @Path("/")
    public Response createIterationWS(IterationDTO iterationDTO) throws Exception {
        iterationBean.create(iterationDTO.getMacAddress(), iterationDTO.getClient().getEmail());

        Iteration iteration = iterationBean.findIteration(iterationDTO.getId());
        return Response.status(Response.Status.CREATED)
                .entity(toDTO(iteration))
                .build();
    }

    @DELETE
    @Path("{id}")
    public Response deleteIterationWS(@PathParam("id") Long id) throws Exception {
        if (iterationBean.delete(id))
            return Response.status(Response.Status.OK)
                    .entity("[Success] Iteration with id \'"+id+"\' was deleted")
                    .build();

        return Response.status(Response.Status.INTERNAL_SERVER_ERROR)
                .build();
    }*/

    private List<IterationDTO> simpleToDTOs(List<Iteration> iterations) {
        return iterations.stream().map(this::simpleToDTO).collect(Collectors.toList());
    }

    private IterationDTO simpleToDTO(Iteration iteration) {
        return new IterationDTO(
            iteration.getId(),
            iteration.getMacAddress(),
            emotionToDTO(iteration.getEmotion()),
            iteration.getCreated_at(),
            countClassifiedFrames(iteration.getFrames()),
            (short) iteration.getFrames().size()
        );
    }

    EmotionDTO emotionToDTO(Emotion emotion) {
        return new EmotionDTO(
                emotion.getName(),
                emotion.getGroup()
        );
    }

    private short countClassifiedFrames(List<Frame> frames){
        short countClassifiedFrames = 0;
        for (Frame frame:frames) {
            if (frame.getEmotion() != null){
                countClassifiedFrames++;
            }
        }
        return countClassifiedFrames;
    }

    private List<IterationDTO> toDTOs(List<Iteration> iterations) {
        return iterations.stream().map(this::toDTO).collect(Collectors.toList());
    }

    private IterationDTO toDTO(Iteration iteration) {
        return new IterationDTO(
            iteration.getId(),
            iteration.getMacAddress(),
            emotionToDTO(iteration.getEmotion()),
            iteration.getCreated_at(),
            clientToDTO(iteration.getClient()),
            framesToDTOs(iteration.getFrames())
        );
    }

    FrameDTO frameToDTO(Frame frame) { return new FrameDTO(
        frame.getId(),
        frame.getName(),
        frame.getPath(),
        frame.getCreateDate());
    }

    List<FrameDTO> framesToDTOs(List<Frame> frames) {
        return frames.stream().map(this::frameToDTO).collect(Collectors.toList());
    }

    private ClientDTO clientToDTO(Client client) {
        return new ClientDTO(
            client.getEmail(),
            client.getName(),
            client.getBirthDate(),
            client.getContact()
        );
    }
}
